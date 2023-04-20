<?php

namespace Drupal\grid\Form;


use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\grid\TwoClick\Constants\Constants;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\NodeType;
use Grid\Constants\GridCSSVariant;
use Grid\Constants\GridCssVariantTable;
use const Grid\Constants\GRID_CSS_VARIANT_FLEXBOX;
use const Grid\Constants\GRID_CSS_VARIANT_NONE;
use const Grid\Constants\GRID_CSS_VARIANT_TABLE;

class SettingsTwoClickForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return [
      Constants::TWO_CLICK_SETTINGS
    ];
  }

  public function getFormId() {
    return "grid_admin_settings";
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $twoClickConfig = $this->config(Constants::TWO_CLICK_SETTINGS);


    $form['two_click_service'] = array(
      '#type' => 'fieldset',
      '#title' => 'Two-click-functionality for video- and html-boxes'
    );
    $form['two_click_service'][Constants::TWO_CLICK_SETTINGS_ENABLE] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable two-click-rendering on all video- and html-boxes'),
      '#default_value' => $twoClickConfig->get(Constants::TWO_CLICK_SETTINGS_ENABLE),
    );
    $form['two_click_service'][Constants::TWO_CLICK_SETTINGS_VIMEO_KEY] = array(
      '#type' => 'textfield',
      '#title' => 'Vimeo API key',
      '#default_value' => $twoClickConfig->get(Constants::TWO_CLICK_SETTINGS_VIMEO_KEY),
    );
    $form['two_click_service'][Constants::TWO_CLICK_SETTINGS_DISCLAIMER_TEXT] = array(
      '#type' => 'textfield',
      '#title' => 'Text for two-click-disclaimer',
      '#default_value' => $twoClickConfig->get(Constants::TWO_CLICK_SETTINGS_DISCLAIMER_TEXT),
    );

    $form['two_click_service'][Constants::TWO_CLICK_SETTINGS_PRIVACY_LINK] = array(
      '#type' => 'textfield',
      '#title' => 'Link to privacy policy',
      '#default_value' => $twoClickConfig->get(Constants::TWO_CLICK_SETTINGS_PRIVACY_LINK),
    );

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    if ($form_state->getValue(Constants::TWO_CLICK_SETTINGS_ENABLE)) {
      grid_delete_video_thumbnails();
    }


    $this->config(Constants::TWO_CLICK_SETTINGS)
      ->set(Constants::TWO_CLICK_SETTINGS_ENABLE, $form_state->getValue(Constants::TWO_CLICK_SETTINGS_ENABLE))
      ->set(Constants::TWO_CLICK_SETTINGS_DISCLAIMER_TEXT, $form_state->getValue(Constants::TWO_CLICK_SETTINGS_DISCLAIMER_TEXT))
      ->set(Constants::TWO_CLICK_SETTINGS_PRIVACY_LINK, $form_state->getValue(Constants::TWO_CLICK_SETTINGS_PRIVACY_LINK))
      ->set(Constants::TWO_CLICK_SETTINGS_VIMEO_KEY, $form_state->getValue(Constants::TWO_CLICK_SETTINGS_VIMEO_KEY))
      ->save();
    parent::submitForm($form, $form_state);
  }


}
