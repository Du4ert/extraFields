<?php

/**
 * @file ExtraFieldsPlugin.inc.php
 *
 * Copyright (c) 2017-2023 Simon Fraser University
 * Copyright (c) 2017-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ExtraFieldsPlugin
 * @brief Plugin class for the ExtraFields plugin.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class ExtraFieldsPlugin extends GenericPlugin
{

    /**
     * @copydoc GenericPlugin::register()
     */
    public function register($category, $path, $mainContextId = NULL)
    {
        $success = parent::register($category, $path);
        if ($success && $this->getEnabled()) {
            // Use a hook to extend the context entity's schema
            HookRegistry::register('Schema::get::publication', array($this, 'addToSchemaPublication'));

            HookRegistry::register('Schema::get::context', array($this, 'addToSchemaContext'));

            // Use a hook to add a field to the title-abstract publicaion settings.
            HookRegistry::register('Form::config::before', array($this, 'addToFormPublication'));

            HookRegistry::register('TemplateManager::display', array($this, 'addCustomVars'));
        }
        return $success;
    }

    /**
     * Provide a name for this plugin
     *
     * The name will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.generic.extraFields.displayName');
    }

    /**
     * Provide a description for this plugin
     *
     * The description will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     *
     * @return string
     */
    public function getDescription()
    {
        return __('plugins.generic.extraFields.description');
    }


    /**
     * Add a property to the context schema
     *
     * @param $hookName string `Schema::get::context`
     * @param $args [[
     * 	@option object Context schema
     * ]]
     */
    public function addToSchemaPublication($hookName, $args)
    {
        $schema = $args[0];
        $schema->properties->funding = (object) [
            'type' => 'string',
            'apiSummary' => true,
            'multilingual' => true,
            'validation' => ['nullable']
        ];
    }

    public function addToSchemaContext($hookName, $args)
    {
        $schema = $args[0];
        $schema->properties->customGuidelines = (object) [
            'type' => 'string',
            'apiSummary' => true,
            'multilingual' => true,
            'validation' => ['nullable']
        ];
    }

    /**
     * Add a form field to a form
     *
     * @param $hookName string `Form::config::before`
     * @param $form FormHandler
     */
    public function addtoFormPublication($hookName, $form)
    {
        if (defined('FORM_METADATA') && $form->id === FORM_METADATA) {
            $this->addFundingField($form);
            return;
        }

        if (defined('FORM_AUTHOR_GUIDELINES') && $form->id === FORM_AUTHOR_GUIDELINES) {
            $this->addCustomAuthorGuidelinesField($form);
            return;
        }
    }


    private function addFundingField($form)
    {
        $context = Application::get()->getRequest()->getContext();

        if (!$context) {
            return;
        }

        $form->addField(new \PKP\components\forms\FieldTextarea('funding', [
            'label' => __('plugins.generic.extraFields.funding'),
            // 'groupId' => 'publishing',
            'size' => 'medium',
            'isMultilingual' => true,
            'value' => $context->getData('funding'),
        ]));
    }


    private function addCustomAuthorGuidelinesField($form)
    {
        $context = Application::get()->getRequest()->getContext();

        if (!$context) {
            return;
        }

        $form->addField(new \PKP\components\forms\FieldRichTextarea('customGuidelines', [
            'label' => __('plugins.generic.extraFields.authorGuidelinesCustom'),
            // 'groupId' => 'publishing',
            'size' => 'medium',
            'isMultilingual' => true,
            'value' => $context->getData('customGuidelines'),
        ]));
    }

    /**
     * Add a var to a template
     *
     * @param $hookName string `TemplateManager::display`
     * @param $templateMgr TemplateManager
     */
    public function addCustomVars($hookName, $args)
    {
        // Retrieve the TemplateManager
        $templateMgr = $args[0];
        $template = $args[1];

        // Don't do anything if we're not loading the right template
        if ($template != 'frontend/pages/submissions.tpl') {
            return;
        }

        $context = Application::get()->getRequest()->getContext();

        $templateMgr->assign([
            'customGuidelines' => $context->getLocalizedData('customGuidelines'),
        ]);
    }
}
