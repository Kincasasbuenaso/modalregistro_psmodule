{*
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
*}



{assign var='controllerName' value=$smarty.get.controller}
{if $controllerName == 'index'}
<p><button type="button"  class="btn btn-primary btn-lg" data-toggle="modal" data-target="#modalRegistroXpec">ver ofertas</button></p>
<div class="row">

<div class="modal fade bs-modalRegistroXpec-modal-lg" tabindex="6000" role="dialog" aria-labelledby="modalRegistroXpecLabel" id="modalRegistroXpec">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
    	<button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeModal"><span aria-hidden="true" class="fancybox-item fancybox-close"></span></button>
    	<a href="{$modalRegistroLink}">
    		<img src="{$imgModalRegistro}" title="{$modalRegistroDes}" id="imgModalRegistro">
    	</a>	
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</div>

{literal}
<script type="text/javascript">
	$(document).ready(function () {
        //$('#ModalOfertXpecHome').modal('hide');
		
		$('#closeModal').on('click',function(e){
			$('#modalRegistroXpec').modal('hide');
			e.preventDefault();
		});

		$('#modalRegistroXpec').modal('show');
    });
</script>
{/literal}
{/if}