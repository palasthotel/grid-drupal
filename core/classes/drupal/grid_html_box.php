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

    //we need to set the encoding specifically via html-head or else german umlauts will not be printed right
    $utf8HTML_Start = '<html><head><meta content="text/html; charset=utf-8" http-equiv="Content-Type"></head><body>';
    $utf8HTML_End = '</body>';


    $mainDom = new DOMDocument('1.0', 'utf-8');
    $mainHTML = $utf8HTML_Start . $html . $utf8HTML_End;

    //suppress errors because DOMDocument throws them if it has to load HTML5...
    $mainDom->loadHTML($mainHTML, LIBXML_NOERROR);
    $iframes = $mainDom->getElementsByTagName('iframe');

    if ($iframes->length > 0) {

      $urlEmbed = new TwoClickEmbedder(\Drupal::service( 'file_system' )->realpath( Constants::THUMBNAIL_FOLDER_PATH ));

      foreach ($iframes as $iframe) {
        $embedCode = $mainDom->saveXML($iframe);
        $url = $iframe->getAttribute('src');
        $urlEmbed->setEmbedCode($embedCode);
        $switchedOutiFrameHtml = $urlEmbed->switchIFrame($url);

        $iframeHTML = $switchedOutiFrameHtml['code'];

        $iframeHTML = $utf8HTML_Start . $iframeHTML . $utf8HTML_End;

        $tempDom = new DOMDocument('1.0', 'utf-8');
        $tempDom->loadHTML($iframeHTML, LIBXML_NOERROR);
        $twoClickContainer = $tempDom->getElementsByTagName('div')->item(0);
        $twoClickContainer = $mainDom->importNode($twoClickContainer, true);
        $iframe->parentNode->appendChild($twoClickContainer);
      }

      //delete iframes from dom
      while ($iframes->length > 0) {
        $iframe = $iframes->item(0);
        $iframe->parentNode->removeChild($iframe);
      }

      //replave the custom html-tags from the output as we do not want a whole new html document, rather just a fragment
      return str_replace([$utf8HTML_Start, $utf8HTML_End ] , '' , $mainDom->saveHTML());
    }

    return $html;

  }

}
