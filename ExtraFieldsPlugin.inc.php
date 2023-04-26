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
        HookRegistry::register('Schema::get::publication', array($this, 'addToSchema'));

        // Use a hook to add a field to the title-abstract publicaion settings.
        HookRegistry::register('Form::config::before', array($this, 'addToForm'));
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
	public function addToSchema($hookName, $args) {
		$schema = $args[0];
        $schema->properties->funding = ( object ) [
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
	public function addtoForm($hookName, $form) {
		if (!defined('FORM_METADATA') || $form->id !== FORM_METADATA) {
			return;
		}

		$context = Application::get()->getRequest()->getContext();

		if (!$context) {
			return;
		}

		$form->addField(new \PKP\components\forms\FieldTextarea('funding', [
			'label' => 'Funding',
			// 'groupId' => 'publishing',
            'size' => 'medium',
            'isMultilingual' => true,
			'value' => $context->getData('funding'),
		]));
	}
}