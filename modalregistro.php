<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class ModalRegistro extends Module
{
	public function __construct()
	{
		$this->name = 'modalregistro';
		$this->tab = 'front_office_features';
		$this->version = '1.4.0';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Modal Registro');
		$this->description = $this->l('Muestra modal para registrarse.');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}

	public function install()
	{
		return
			parent::install() &&
			$this->registerHook('displayFooter') &&
			$this->registerHook('displayHeader') &&
			$this->registerHook('actionObjectLanguageAddAfter') &&
			$this->installFixtures() &&
			$this->disableDevice(Context::DEVICE_MOBILE);
	}

	public function hookActionObjectLanguageAddAfter($params)
	{
		return $this->installFixture((int)$params['object']->id, Configuration::get('MODAL_REGISTRO_XPEC', (int)Configuration::get('PS_LANG_DEFAULT')));
	}

	protected function installFixtures()
	{
		$languages = Language::getLanguages(false);
		foreach ($languages as $lang)
			$this->installFixture((int)$lang['id_lang'], 'sale70.png');

		return true;
	}

	protected function installFixture($id_lang, $image = null)
	{
		$values['MODAL_REGISTRO_XPEC'][(int)$id_lang] = $image;
		$values['MODAL_REGISTRO_LINK_XPEC'][(int)$id_lang] = '';
		$values['MODAL_REGISTRO_DESC_XPEC'][(int)$id_lang] = '';
		Configuration::updateValue('MODAL_REGISTRO_XPEC', $values['MODAL_REGISTRO_XPEC']);
		Configuration::updateValue('MODAL_REGISTRO_LINK_XPEC', $values['MODAL_REGISTRO_LINK_XPEC']);
		Configuration::updateValue('MODAL_REGISTRO_DESC_XPEC', $values['MODAL_REGISTRO_DESC_XPEC']);

	}

	public function uninstall()
	{
		Configuration::deleteByName('MODAL_REGISTRO_XPEC');
		Configuration::deleteByName('MODAL_REGISTRO_LINK_XPEC');
		Configuration::deleteByName('MODAL_REGISTRO_DESC_XPEC');

		return parent::uninstall();
	}

	public function hookDisplayFooter($params)
	{
		if (!$this->isCached('modalregistro.tpl', $this->getCacheId()))
		{
			$imgModalRegistro = Configuration::get('MODAL_REGISTRO_XPEC', $this->context->language->id);

			if ($imgModalRegistro && file_exists(_PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$imgModalRegistro))
				$this->smarty->assign('imgModalRegistro', $this->context->link->protocol_content.Tools::getMediaServer($imgModalRegistro).$this->_path.'img/'.$imgModalRegistro);

			$this->smarty->assign(array(
				'modalRegistroLink' => Configuration::get('MODAL_REGISTRO_LINK_XPEC', $this->context->language->id),
				'modalRegistroDes' => Configuration::get('MODAL_REGISTRO_DESC_XPEC', $this->context->language->id)
			));
		}

		return $this->display(__FILE__, 'modalregistro.tpl', $this->getCacheId());
	}

	public function hookDisplayBanner($params)
	{
		return $this->hookDisplayTop($params);
	}

	/*public function hookDisplayFooter($params)
	{
		return $this->hookDisplayTop($params);
	}*/

	public function hookDisplayHeader($params)
	{
		$this->context->controller->addCSS($this->_path.'modalregistro.css', 'all');
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitModalRegistro'))
		{
			$languages = Language::getLanguages(false);
			$values = array();
			$update_images_values = false;

			foreach ($languages as $lang)
			{
				if (isset($_FILES['MODAL_REGISTRO_XPEC_'.$lang['id_lang']])
					&& isset($_FILES['MODAL_REGISTRO_XPEC_'.$lang['id_lang']]['tmp_name'])
					&& !empty($_FILES['MODAL_REGISTRO_XPEC_'.$lang['id_lang']]['tmp_name']))
				{
					if ($error = ImageManager::validateUpload($_FILES['MODAL_REGISTRO_XPEC_'.$lang['id_lang']], 4000000))
						return $error;
					else
					{
						$ext = substr($_FILES['MODAL_REGISTRO_XPEC_'.$lang['id_lang']]['name'], strrpos($_FILES['MODAL_REGISTRO_XPEC_'.$lang['id_lang']]['name'], '.') + 1);
						$file_name = md5($_FILES['MODAL_REGISTRO_XPEC_'.$lang['id_lang']]['name']).'.'.$ext;

						if (!move_uploaded_file($_FILES['MODAL_REGISTRO_XPEC_'.$lang['id_lang']]['tmp_name'], dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$file_name))
							return $this->displayError($this->l('An error occurred while attempting to upload the file.'));
						else
						{
							if (Configuration::hasContext('MODAL_REGISTRO_XPEC', $lang['id_lang'], Shop::getContext())
								&& Configuration::get('MODAL_REGISTRO_XPEC', $lang['id_lang']) != $file_name)
								@unlink(dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.Configuration::get('MODAL_REGISTRO_XPEC', $lang['id_lang']));

							$values['MODAL_REGISTRO_XPEC'][$lang['id_lang']] = $file_name;
						}
					}

					$update_images_values = true;
				}

				$values['MODAL_REGISTRO_LINK_XPEC'][$lang['id_lang']] = Tools::getValue('MODAL_REGISTRO_LINK_XPEC_'.$lang['id_lang']);
				$values['MODAL_REGISTRO_DESC_XPEC'][$lang['id_lang']] = Tools::getValue('MODAL_REGISTRO_DESC_XPEC_'.$lang['id_lang']);

			}

			if ($update_images_values)
				Configuration::updateValue('MODAL_REGISTRO_XPEC', $values['MODAL_REGISTRO_XPEC']);


			Configuration::updateValue('MODAL_REGISTRO_LINK_XPEC', $values['MODAL_REGISTRO_LINK_XPEC']);
			Configuration::updateValue('MODAL_REGISTRO_DESC_XPEC', $values['MODAL_REGISTRO_DESC_XPEC']);


			$this->_clearCache('modalregistro.tpl');
			return $this->displayConfirmation($this->l('The settings have been updated.'));
		}
		return '';
	}

	public function getContent()
	{
		return $this->postProcess().$this->renderForm();
	}

	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'file_lang',
						'label' => $this->l('banner image 1'),
						'name' => 'MODAL_REGISTRO_XPEC',
						'desc' => $this->l('Se recomienda que las dimensiones de la imagen sea de '),
						'lang' => true,
					),
					array(
						'type' => 'text',
						'lang' => true,
						'label' => $this->l('Banner Link 1'),
						'name' => 'MODAL_REGISTRO_LINK_XPEC',
						'desc' => $this->l('Link URL del Banner 1')
					),
					array(
						'type' => 'text',
						'lang' => true,
						'label' => $this->l('Banner description '),
						'name' => 'MODAL_REGISTRO_DESC_XPEC',
						'desc' => $this->l('Descripcion del Banner 1')
					)

				),
				'submit' => array(
					'title' => $this->l('Save')
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->module = $this;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitModalRegistro';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'uri' => $this->getPathUri(),
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		$languages = Language::getLanguages(false);
		$fields = array();

		foreach ($languages as $lang)
		{
			$fields['MODAL_REGISTRO_XPEC'][$lang['id_lang']] = Tools::getValue('MODAL_REGISTRO_XPEC_'.$lang['id_lang'], Configuration::get('MODAL_REGISTRO_XPEC', $lang['id_lang']));
			$fields['MODAL_REGISTRO_LINK_XPEC'][$lang['id_lang']] = Tools::getValue('MODAL_REGISTRO_LINK_XPEC_'.$lang['id_lang'], Configuration::get('MODAL_REGISTRO_LINK_XPEC', $lang['id_lang']));
			$fields['MODAL_REGISTRO_DESC_XPEC'][$lang['id_lang']] = Tools::getValue('MODAL_REGISTRO_DESC_XPEC_'.$lang['id_lang'], Configuration::get('MODAL_REGISTRO_DESC_XPEC', $lang['id_lang']));

		}

		return $fields;
	}
}
