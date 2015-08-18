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
        if(!empty($node->tnid))
        {
            $nodes=$node->getTranslationLanguages(false);

            $form=array();
            $form['question']=array(
                '#type'=>'markup',
                '#markup'=>'<div>'.t('There is no Grid.').'</div>',
            );
            $options=array();
            $options[-1]='Boot new Grid';
            foreach($nodes as $language)
            {
                $localized=$node->getTranslation($language);
                if(isset($localized->grid))
                {
                    $options[$lnode->nid]=t('Clone Grid from ').$localized->title.'['.$language.']';
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
            return $form;
        }
        else
        {
            $form=array();
            $form['question']=array(
                '#type'=>'markup',
                '#markup'=>'<div>'.t('There is no Grid. Boot one?').'</div>',
            );
            $form['submit']=array(
                '#type'=>'submit',
                '#value'=>'Create Grid',
                '#executes_submit_callback'=>TRUE,
            );
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
        if(!empty($node->tnid))
        {
            $clone=$form_state['values']['options'];
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

                db_insert('grid_nodes')->fields(array('nid','grid_id'))->values(array('nid'=>$form_state['nid'],'grid_id'=>$id))->execute();
            }
            else
            {
                $clonenode=Node::load($clone);
                $grid=$clonenode->grid;
                $cloned=$grid->cloneGrid();
                db_insert('grid_nodes')->fields(array('nid','grid_id'))->values(array('nid'=>$node->nid,'grid_id'=>$cloned->gridid))->execute();
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

            db_insert('grid_nodes')->fields(array('nid','grid_id'))->values(array('nid'=>$nid,'grid_id'=>$id))->execute();
        }
    }
}