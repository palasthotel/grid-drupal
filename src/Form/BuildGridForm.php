<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 17.08.15
 * Time: 16:14
 */

namespace Drupal\grid\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\node\Entity\Node;

class BuildGridForm extends FormBase
{

    /**
     * Returns a unique string identifying the form.
     *
     * @return string
     *   The unique string identifying the form.
     */
    public function getFormId()
    {
        return 'grid_build_grid';
    }

    /**
     * Form constructor.
     *
     * @param array $form
     *   An associative array containing the structure of the form.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   The current state of the form.
     *
     * @return array
     *   The form structure.
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $buildinfo=$form_state->getBuildInfo();
        $nid=$buildinfo['args'][0];
        //$form_state['nid']=$nid;

        /** @var Node $node */
        $node = Node::load($nid);
        if(count($node->getTranslationLanguages(true))>1)
        {
            $nodes=$node->getTranslationLanguages(true);
            $language=\Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT);
            $form=array();
            if(!$node->hasTranslation($language->getId()) && $node->language()->getId() != $language->getId()) {
                $form['question']=array(
                    '#type'=>'markup',
                    '#markup'=>'<div>'.t('The current node is not yet translated to the current language. Please create a translation first.').'</div>',
                );
            } else {
                $form['question']=array(
                    '#type'=>'markup',
                    '#markup'=>'<div>'.t('There is no Grid.').'</div>',
                );
                $options=array();
                $options[-1]='Boot new Grid';
                foreach($nodes as $language)
                {
                    $localized=$node->getTranslation($language->getId());
                    $gridid=grid_get_grid_by_nid($nid,$language->getId());
                    if($gridid!==FALSE)
                    {
                        $options[$language->getId()]=t('Clone Grid from ').$localized->getTitle().'['.$language->getName().']';
                    }
                }
                $form['options']=array(
                    '#type'=>'radios',
                    '#default_value'=>-1,
                    '#options'=>$options
                );
                $form['submit']=array(
                    '#type'=>'submit',
                    '#value'=>'Create Grid',
                    '#executes_submit_callback'=>TRUE,
                );
            }
            return $form;
        }
        else
        {
            $form=array();
            $language=\Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT);
            $form=array();
            if(!$node->hasTranslation($language->getId()) && $node->language()->getId() != $language->getId()) {
                $form['question']=array(
                    '#type'=>'markup',
                    '#markup'=>'<div>'.t('The current node is not yet translated to the current language. Please create a translation first.').'</div>',
                );
            } else {
                $form['question']=array(
                    '#type'=>'markup',
                    '#markup'=>'<div>'.t('There is no Grid. Boot one?').'</div>',
                );
                $form['submit']=array(
                    '#type'=>'submit',
                    '#value'=>'Create Grid',
                    '#executes_submit_callback'=>TRUE,
                );
            }
            return $form;
        }
    }

    /**
     * Form submission handler.
     *
     * @param array $form
     *   An associative array containing the structure of the form.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   The current state of the form.
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $nid=$form_state->getBuildInfo()['args'][0];
        /** @var Node $node */
        $node=Node::load($nid);
        $storage=grid_get_storage();
        $config=$this->config("grid.settings");
        if(count($node->getTranslationLanguages(true))>1)
        {
            $clone=$form_state->getValue('options');
            if($clone==-1)
            {
                $id=$storage->createGrid();
                if($node->getType()==$config->get("sidebar_content"))
                {
                    $grid=$storage->loadGrid($id);
                    $grid->insertContainer("SC-4",0);
                }
                else if($config->get("default_container")!='__NONE__')
                {
                    $grid=$storage->loadGrid($id);
                    $grid->insertContainer($config->get("default_container"),0);
                }

                \Drupal::Database()->insert('grid_nodes')
                    ->fields(array('nid','grid_id','langcode'))
                    ->values(array('nid'=>$nid,'grid_id'=>$id,'langcode'=>\Drupal::languageManager()->getCurrentLanguage()->getId()))
                    ->execute();
            }
            else
            {
                $grid_id=grid_get_grid_by_nid($nid,$clone);
                $grid=grid_get_storage()->loadGrid($grid_id,FALSE);
                $cloned=$grid->cloneGrid();
                \Drupal::Database()->insert('grid_nodes')
                    ->fields(array('nid','grid_id','langcode'))
                    ->values(array('nid'=>$nid,'grid_id'=>$cloned->gridid,'langcode'=>\Drupal::languageManager()->getCurrentLanguage()->getId()))
                    ->execute();
            }
        }
        else
        {
            $id=$storage->createGrid();
            if($node->getType()==$config->get("sidebar_content"))
            {
                $grid=$storage->loadGrid($id);
                $grid->insertContainer("sc-1d3",0);
            }
            else if($config->get("default_container")!='__NONE__')
            {
                $grid=$storage->loadGrid($id);
                $grid->insertContainer($config->get("default_container"),0);
            }
            $langcode=$node->get("langcode")->getValue()[0]['value'];
            \Drupal::Database()->insert('grid_nodes')
                ->fields(array('nid','grid_id','langcode'))
                ->values(array('nid'=>$nid,'grid_id'=>$id,'langcode'=>$langcode))
                ->execute();
        }
    }
}
