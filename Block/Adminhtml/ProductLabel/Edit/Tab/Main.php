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

    namespace Hippiemonkeys\ModificationCodazonProductLabel\Block\Adminhtml\ProductLabel\Edit\Tab;

    use Codazon\ProductLabel\Block\Adminhtml\ProductLabel\Edit\Tab\Main as ParentMain,
        Magento\Backend\Block\Template\Context,
        Magento\Framework\Registry,
        Magento\Framework\Data\FormFactory,
        Magento\Customer\Api\GroupRepositoryInterface,
        Magento\Framework\Api\SearchCriteriaBuilder,
        Magento\Framework\Convert\DataObject,
        Magento\Store\Model\System\Store,
        Codazon\ProductLabel\Model\Wysiwyg\Config as WysiwygConfig,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface,
        Magento\Backend\Block\Widget\Form\Generic;

    class Main
    extends ParentMain
    {
        protected const CONFIG_PATH_MODIFICATION_STATUS = 'custom_variable_status';


        /**
         * Constructor
         *
         * @access public
         *
         * @param \Magento\Backend\Block\Template\Context $context
         * @param \Magento\Framework\Registry $registry
         * @param \Magento\Framework\Data\FormFactory $formFactory
         * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
         * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
         * @param \Magento\Framework\Convert\DataObject $objectConverter
         * @param \Magento\Store\Model\System\Store $systemStore
         * @param \Codazon\ProductLabel\Model\Wysiwyg\Config $wysiwygConfig
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         * @param array $data
         */
        public function __construct(
            Context $context,
            Registry $registry,
            FormFactory $formFactory,
            GroupRepositoryInterface $groupRepository,
            SearchCriteriaBuilder $searchCriteriaBuilder,
            DataObject $objectConverter,
            Store $systemStore,
            WysiwygConfig $wysiwygConfig,
            ConfigInterface $config,
            array $data = []
        )
        {
            parent::__construct(
                $context,
                $registry,
                $formFactory,
                $groupRepository,
                $searchCriteriaBuilder,
                $objectConverter,
                $systemStore,
                $wysiwygConfig,
                $data
            );
            $this->_config = $config;
        }


        /**
         * @inheritdoc
         */
        protected function _prepareForm()
        {
            $prepareForm = null;
            if($this->getIsActive())
            {
                $model = $this->_coreRegistry->registry('productlabel');
                $form = $this->_formFactory->create();
                $form->setHtmlIdPrefix('label_');

                $fieldset = $form->addFieldset(
                    'base_fieldset',
                    ['legend' => __('General Information'), 'class' => 'fieldset-wide']
                );

                if ($model->getEntityId())
                {
                    $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
                }

                $fieldset->addField('store', 'hidden', ['name' => 'store']);
                $fieldset->addField(
                    'title',
                    'text',
                    ['name' => 'title', 'label' => __('Label Title'), 'title' => __('Label Title'), 'required' => true]
                );
                $fieldset->addField(
                    'is_active',
                    'select',
                    [
                        'label' => __('Status'),
                        'title' => __('Status'),
                        'name' => 'is_active',
                        'required' => true,
                        'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
                    ]
                );

                if (!$model->getId())
                {
                    $model->setData('is_active', '1');
                }

                $field = $fieldset->addField(
                    'content',
                    'textarea',
                    [
                        'label' => __('Label Content'),
                        'title' => __('Label Content'),
                        'name' => 'content',
                        'required' => false
                    ]
                );
                $renderer = $this->getLayout()->createBlock(
                    'Hippiemonkeys\ModificationCodazonProductLabel\Block\Adminhtml\ProductLabel\AbstractHtmlField\Variables'
                );
                $field->setRenderer($renderer);
                $field = $fieldset->addField(
                    'label_image',
                    'hidden',
                    [
                        'label' => __('Label Image'),
                        'title' => __('Label Image'),
                        'name' => 'label_image',
                        'class' => 'image_type'
                    ]
                );
                $renderer = $this->getLayout()->createBlock(
                    'Codazon\ProductLabel\Block\Adminhtml\ProductLabel\AbstractHtmlField\Images'
                );

                $field->setRenderer($renderer);
                $field = $fieldset->addField(
                    'label_background',
                    'hidden',
                    [
                        'label' => __('Label Background'),
                        'title' => __('Label Background'),
                        'name' => 'label_background',
                        'class' => 'image_type'
                    ]
                );

                $renderer = $this->getLayout()->createBlock(
                    'Codazon\ProductLabel\Block\Adminhtml\ProductLabel\AbstractHtmlField\Images'
                );

                $field->setRenderer($renderer);
                $fieldset->addField(
                    'custom_class',
                    'text',
                    [
                        'label' => __('Custom Class'),
                        'title' => __('Custom Class'),
                        'name' => 'custom_class'
                    ]
                );
                $fieldset->addField(
                    'custom_css',
                    'textarea',
                    [
                        'label' => __('Custom CSS'),
                        'title' => __('Custom CSS'),
                        'name' => 'custom_css'
                    ]
                );
                $form->setDataObject($model);
                $form->setValues($model->getData());
                $this->setForm($form);

                $prepareForm = Generic::_prepareForm();
            }
            else
            {
                $prepareForm = parent::_prepareForm();
            }

            return $prepareForm;
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