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

    namespace Hippiemonkeys\ModificationCodazonProductLabel\Block;

    use Magento\Framework\View\Element\Template\Context,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface,
        Codazon\ProductLabel\Block\ProductLabel as ParentProductLabel;

    class ProductLabel
    extends ParentProductLabel
    {
        protected const CONFIG_PATH_MODIFICATION_STATUS = 'custom_variable_status';

        /**
         * Constructor
         *
         * @access public
         *
         * @param \Magento\Framework\View\Element\Template\Context $context
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         * @param array $data
         */
        public function __construct(
            Context $context,
            ConfigInterface $config,
            array $data = []
        )
        {
            parent::__construct($context, $data);

            $this->_config = $config;

            if($this->getIsActive())
            {
                $this->_template = 'Hippiemonkeys_ModificationCodazonProductLabel::productlabel.phtml';
            }
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
    }
?>