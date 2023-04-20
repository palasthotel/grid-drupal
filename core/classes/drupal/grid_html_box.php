<?php
/**
 * @author Palasthotel <rezeption@palasthotel.de>
 * @copyright Copyright (c) 2014, Palasthotel
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @package Palasthotel\Grid
 */
/**
* HTML-Box contents is considered a static content.
*/

use Drupal\grid\TwoClick\Constants\Constants;
use Drupal\grid\TwoClick\TwoClickEmbedder;

class grid_html_box extends grid_static_base_box {

  private bool $twoClickIsActive;
	/**
	* Sets box type
	*
	* @return string
	*/
	public function type() {
		return 'html';
	}

	/**
	* Class constructor
	*
	* Constructor initializes editor widgets.
	*/
	public function __construct() {
		parent::__construct();
		$this->content->html='';
    $config = \Drupal::config(Constants::TWO_CLICK_SETTINGS);
    $this->twoClickIsActive = (bool) $config->get(Constants::TWO_CLICK_SETTINGS_ENABLE);
	}

	/**
	* Box determins its menu label and renders its content in here.
	*
	* @param boolean $editmode
	*
	* @return string
	*/
	public function build($editmode) {

    if ($this->content->html !== "" && $this->twoClickIsActive ) return $this->checkForIframe($this->content->html);

    return $this->content->html;
	}

	/**
	* Determines editor widgets used in backend
	*
	* @return array
	*/
	public function contentStructure () {
		$cs = parent::contentStructure();
		return array_merge($cs, array(
			array(
				'key'=>'html',
				'label'=>t('Text'),
				'type'=>'html'
			),
		));
	}

  private function checkForIframe($html){

    $mainDom = new DOMDocument('1.0', 'utf-8');
    //suppress errors because DOMDocument throws them if it has to load HTML5...
    $mainDom->loadHTML($html, LIBXML_NOERROR);
    $iframes = $mainDom->getElementsByTagName('iframe');

    if ($iframes->length > 0) {

      $urlEmbed = new TwoClickEmbedder(\Drupal::service( 'file_system' )->realpath( Constants::THUMBNAIL_FOLDER_PATH ));

      foreach ($iframes as $iframe) {
        $embedCode = $mainDom->saveXML($iframe);
        $url = $iframe->getAttribute('src');
        $urlEmbed->setEmbedCode($embedCode);
        $switchedOutiFrameHtml = $urlEmbed->switchIFrame($url);


        $tempDom = new DOMDocument('1.0', 'utf-8');
        $tempDom->loadHTML($switchedOutiFrameHtml['code'], LIBXML_NOERROR);
        $twoClickContainer = $tempDom->getElementsByTagName('div')->item(0);
        $twoClickContainer = $mainDom->importNode($twoClickContainer, true);
        $iframe->parentNode->appendChild($twoClickContainer);
      }

      //delete iframes from dom
      while ($iframes->length > 0) {
        $iframe = $iframes->item(0);
        $iframe->parentNode->removeChild($iframe);
      }

      return $mainDom->saveHTML();
    }

    return $html;

  }

}
