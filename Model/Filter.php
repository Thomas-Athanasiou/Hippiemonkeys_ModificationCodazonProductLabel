<?php
    /**
     * @Thomas-Athanasiou
     *
     * @author Thomas Athanasiou {thomas@hippiemonkeys.com}
     * @link https://hippiemonkeys.com
     * @link https://github.com/Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Intelligence EE All Rights Reserved.
     * @license http://www.gnu.org/licenses/ GNU General Public License, version 3
     * @package Hippiemonkeys_ModificationCodazonProductLabel
     */

    declare(strict_types=1);

    namespace  Hippiemonkeys\ModificationCodazonProductLabel\Model;

    use Codazon\ProductLabel\Model\Filter as ParentFilter,
        Magento\Framework\Stdlib\StringUtils,
        Psr\Log\LoggerInterface,
        Magento\Framework\Escaper,
        Magento\Framework\View\Asset\Repository,
        Magento\Framework\App\Config\ScopeConfigInterface,
        Magento\Variable\Model\VariableFactory,
        Magento\Store\Model\StoreManagerInterface,
        Magento\Framework\View\LayoutInterface,
        Magento\Framework\View\LayoutFactory,
        Magento\Framework\App\State,
        Magento\Framework\UrlInterface,
        Magento\Variable\Model\Source\Variables,
        Magento\Framework\Filter\VariableResolverInterface,
        Magento\Email\Model\Template\Css\Processor as CssProcessor,
        Magento\Framework\Filesystem,
        Magento\Framework\Css\PreProcessor\Adapter\CssInliner,
        Magento\Store\Model\Information as StoreInformation,
        Magento\Framework\Translate\Inline\StateInterface,
        Magento\Framework\Api\SimpleDataObjectConverter,
        Magento\Framework\DataObject,
        Magento\Eav\Api\AttributeRepositoryInterface,
        Magento\Framework\Api\SearchCriteriaBuilder,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface;

    class Filter
    extends ParentFilter
    {
        protected const
            CONFIG_PATH_MODIFICATION_STATUS = 'custom_variable_status',

            VARIABLE_FORMAT = '{{var product.%s}}';

        /**
         * Constructor
         *
         * @access public
         *
         * @param \Magento\Framework\Stdlib\StringUtils $string
         * @param \Psr\Log\LoggerInterface $logger
         * @param \Magento\Framework\Escaper $escaper
         * @param \Magento\Framework\View\Asset\Repository $assetRepo
         * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
         * @param \Magento\Variable\Model\VariableFactory $coreVariableFactory
         * @param \Magento\Store\Model\StoreManagerInterface $storeManager
         * @param \Magento\Framework\View\LayoutInterface $layout
         * @param \Magento\Framework\View\LayoutFactory $layoutFactory
         * @param \Magento\Framework\App\State $appState
         * @param \Magento\Framework\UrlInterface $urlModel
         * @param \Magento\Variable\Model\Source\Variables $configVariables
         * @param \Magento\Framework\Filter\VariableResolverInterface $variableResolver
         * @param \Magento\Email\Model\Template\Css\Processor $cssProcessor
         * @param \Magento\Framework\Filesystem $pubDirectory
         * @param \Magento\Framework\Css\PreProcessor\Adapter\CssInliner $cssInliner
         * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
         * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         * @param array $variables
         * @param array $directiveProcessors
         * @param \Magento\Store\Model\Information|null $storeInformation
         * @param \Magento\Framework\Translate\Inline\StateInterface|null $inlineTranslationState
         */
        public function __construct(
            StringUtils $string,
            LoggerInterface $logger,
            Escaper $escaper,
            Repository $assetRepo,
            ScopeConfigInterface $scopeConfig,
            VariableFactory $coreVariableFactory,
            StoreManagerInterface $storeManager,
            LayoutInterface $layout,
            LayoutFactory $layoutFactory,
            State $appState,
            UrlInterface $urlModel,
            Variables $configVariables,
            VariableResolverInterface $variableResolver,
            CssProcessor $cssProcessor,
            Filesystem $pubDirectory,
            CssInliner $cssInliner,
            AttributeRepositoryInterface $attributeRepository,
            SearchCriteriaBuilder $searchCriteriaBuilder,
            ConfigInterface $config,
            array $variables = [],
            array $directiveProcessors = [],
            ?StoreInformation $storeInformation = null,
            StateInterface $inlineTranslationState = null
        )
        {
            parent::__construct(
                $string,
                $logger,
                $escaper,
                $assetRepo,
                $scopeConfig,
                $coreVariableFactory,
                $storeManager,
                $layout,
                $layoutFactory,
                $appState,
                $urlModel,
                $configVariables,
                $variableResolver,
                $cssProcessor,
                $pubDirectory,
                $cssInliner,
                $variables,
                $directiveProcessors,
                $storeInformation,
                $inlineTranslationState
            );

            $this->_config = $config;
            $this->_productAttributes = $this->getIsActive()
                ?  $attributeRepository->getList('catalog_product', $searchCriteriaBuilder->create())->getItems()
                : [];
        }

        /**
         * @inheritdoc
         */
        public function getCustomVariable()
        {
            $array = parent::getCustomVariable();

            if($this->getIsActive())
            {
                foreach($this->getProductAttributes() as $productAttribute)
                {
                    $array[] = \sprintf(static::VARIABLE_FORMAT, $productAttribute->getAttributeCode());
                }
            }

            return $array;
        }

        /**
         * @inheritdoc
         */
        public function getVariables()
        {
            $data = parent::getVariables();

            if($this->getIsActive())
            {
                foreach($this->getProductAttributes() as $productAttribute)
                {
                    $data[0]['value'][] = [
                        'label' => $productAttribute->getFrontendLabel(),
                        'value' => \sprintf(static::VARIABLE_FORMAT, $productAttribute->getAttributeCode())
                    ];
                }
            }

            return $data;
        }

        /**
         * @inheritdoc
         */
        public function getCustomVariableValue($construction, $_product)
        {
            $type = \trim($construction[2]);

            $attributeCodeParts = \explode('.', $type, 2);

            $value = (count($attributeCodeParts) > 1 && $attributeCodeParts[0] === 'product')
                ? $this->getProductAttributeText($attributeCodeParts[1], $_product)
                : parent::getCustomVariableValue($construction, $_product);

            return $value;
        }

        /**
         * Gets wether the indexer modification is active or not.
         *
         * @access protected
         *
         * @return bool
         */
        protected function getIsActive(): bool
        {
            return $this->getConfig()->getModuleStatus() && $this->getModificationStatus();
        }

        /**
         * Gets get product attribute text
         *
         * @access protected
         *
         * @param string $attributeCode
         * @param \Magento\Framework\DataObject $product
         *
         * @return string
         */
        protected function getProductAttributeText(string $attributeCode, DataObject $product): string
        {
            $text = '';

            $value = $product->{ $this->getGetterMethodName($attributeCode) }();
            if(\is_scalar($value))
            {
                if(\is_float($value))
                {
                    $text = (string) \floatval($value);
                }
                if(is_numeric($value))
                {
                    $text = (string) \floatval((float) $value);
                }
                else
                {
                    $text = $value;
                }
            }

            return $text;
        }

        /**
         * Gets Modification Status flag
         *
         * @access protected
         *
         * @return bool
         */
        protected function getModificationStatus(): bool
        {
            return $this->getConfig()->getFlag(static::CONFIG_PATH_MODIFICATION_STATUS);
        }

        /**
         * Config property
         *
         * @access private
         *
         * @var \Hippiemonkeys\Core\Api\Helper\ConfigInterface $_config
         */
        private $_config;

        /**
         * Gets Config
         *
         * @access protected
         *
         * @return \Hippiemonkeys\Core\Api\Helper\ConfigInterface
         */
        protected function getConfig(): ConfigInterface
        {
            return $this->_config;
        }

        /**
         * Get getter name based on field name
         *
         * @access private
         *
         * @param string $fieldName
         *
         * @return string
         */
        private function getGetterMethodName(string $fieldName): string
        {
            return \sprintf(
                'get%s',
                SimpleDataObjectConverter::snakeCaseToUpperCamelCase($fieldName)
            );
        }

        /**
         * Product Attributes property
         *
         * @access private
         *
         * @var array $_productAttributes
         */
        private $_productAttributes;

        /**
         * Gets Product Attributes
         *
         * @access private
         *
         * @return array
         */
        private function getProductAttributes(): array
        {
            return $this->_productAttributes;
        }
    }
?>