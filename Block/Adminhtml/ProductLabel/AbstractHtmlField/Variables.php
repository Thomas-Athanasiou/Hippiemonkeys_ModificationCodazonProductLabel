<?php
    /**
     * @Thomas-Athanasiou
     *
     * @author Thomas Athanasiou {thomas@hippiemonkeys.com}
     * @link https://hippiemonkeys.com
     * @link https://github.com/Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Inteligence EE All Rights Reserved.
     * @license http://www.gnu.org/licenses/ GNU General Public License, version 3
     * @package Hippiemonkeys_ModificationCodazonProductLabel
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\ModificationCodazonProductLabel\Block\Adminhtml\ProductLabel\AbstractHtmlField;

    use Magento\Backend\Block\Template\Context,
        Codazon\ProductLabel\Block\Adminhtml\ProductLabel\AbstractHtmlField\Variables as ParentVariables,
        Magento\Eav\Api\AttributeRepositoryInterface,
        Magento\Framework\Api\SearchCriteriaBuilder,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface;

    class Variables
    extends ParentVariables
    {
        protected const
            CONFIG_PATH_MODIFICATION_STATUS = 'custom_variable_status',

            VARIABLE_FORMAT = '{{var product.%s}}';


        /**
         * @inheritdoc
         */
        protected $_template = 'Codazon_ProductLabel::productlabel/content_html/renderer/fieldset/variables.phtml';

        /**
         * Constructor
         *
         * @access public
         *
         * @param \Magento\Backend\Block\Template\Context $contect
         * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
         * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         * @param array $data
         */
        public function __construct(
            Context $context,
            AttributeRepositoryInterface $attributeRepository,
            SearchCriteriaBuilder $searchCriteriaBuilder,
            ConfigInterface $config,
            array $data = []
        )
        {
            parent::__construct($context, $data);

            $this->_config = $config;
            $this->_productAttributes = $this->getIsActive()
                ?  $attributeRepository->getList('catalog_product', $searchCriteriaBuilder->create())->getItems()
                : [];
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