<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 17.08.15
 * Time: 10:23
 */

namespace Drupal\grid\Form;


use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\NodeType;

class SettingsForm extends ConfigFormBase
{

    protected function getEditableConfigNames()
    {
        return [
            'grid.settings'
        ];
    }

    public function getFormId()
    {
        return "grid_admin_settings";
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config=$this->config("grid.settings");

        $form['defaultcontainer']=array(
            '#type'=>'fieldset',
            '#title'=>t('Which Container should be placed on empty grids?'),
        );

        $options=array();
        $options['__NONE__']=t('None - leave empty');
        $storage=grid_get_storage();
        $containers=$storage->fetchContainerTypes();
        foreach($containers as $container)
        {
            if(strpos($container['type'],"c-")===0)
            {
                $options[$container['type']]=$container['type'];
            }
        }

        $form['defaultcontainer']['default_container']=array(
            '#title'=>t('Default Container'),
            '#type'=>'select',
            '#options'=>$options,
            '#default_value'=>$config->get("default_container"),
        );

        $form['defaultstyles']=array(
            '#title'=>t('Set Styles which should be applied to everything by default.'),
            '#type'=>'fieldset',
        );

        $storage=grid_get_storage();
        $styles=$storage->fetchContainerStyles();
        $array=array();
        $array['__NONE__']='None';
        foreach($styles as $idx=>$elem)
        {
            $array[$elem['slug']]=$elem['title'];
        }
        $form['defaultstyles']['default_container_style']=array(
            '#type'=>'select',
            '#title'=>'Container Style',
            '#options'=>$array,
            '#default_value'=>$config->get('default_container_style'),
        );

        $styles=$storage->fetchSlotStyles();
        $array=array();
        $array['__NONE__']='None';
        foreach($styles as $idx=>$elem)
        {
            $array[$elem['slug']]=$elem['title'];
        }
        $form['defaultstyles']['default_slot_style']=array(
            '#type'=>'select',
            '#title'=>'Slot Style',
            '#options'=>$array,
            '#default_value'=>$config->get('default_slot_style'),
        );

        $styles=$storage->fetchBoxStyles();
        $array=array();
        $array['__NONE__']='None';
        foreach($styles as $idx=>$elem)
        {
            $array[$elem['slug']]=$elem['title'];
        }
        $form['defaultstyles']['default_box_style']=array(
            '#type'=>'select',
            '#title'=>'Box Style',
            '#options'=>$array,
            '#default_value'=>$config->get('default_box_style'),
        );

        $form['nodetypes']=array(
            '#type'=>'fieldset',
            '#title'=>t('Grid support for the following node types'),
        );

        /** @var NodeType[] $nodetypes */
        $nodetypes=NodeType::loadMultiple();
        $keys=array_keys($nodetypes);
        foreach($keys as $key)
        {
            $form['nodetypes']['grid_'.$key.'_enabled']=array(
                '#type'=>'checkbox',
                '#default_value'=>in_array($key,$config->get("enabled_node_types")),
                '#title'=>$key,
            );
        }

        $form['sidebar']=array(
            '#type'=>'fieldset',
            '#title'=>t('Content to be used as a sidebar'),
        );
        $options=array();
        $options['sidebars_disabled_i_just_have_to_ensure_no_nodetype_is_called_like_this_lets_hope_no_one_is_ever_that_crazy']='None';
        foreach($keys as $key)
        {
            if(in_array($key,$config->get("enabled_node_types")))
            {
                $options[$key]=$key;
            }
        }

        $form['sidebar']['sidebar_content']=array(
            '#type'=>'select',
            '#title'=>'Node Type',
            '#options'=>$options,
            '#default_value'=>$config->get('sidebar_content'),
        );

        $form['displays']=array(
            '#type'=>'fieldset',
            '#title'=>t('Supported Displays for nodes within grid'),
        );
        /** @var EntityTypeInterface $info */
        $view_modes=\Drupal::entityManager()->getViewModes("node");

        foreach($view_modes as $key=>$viewmode)
        {
            $form['displays']["grid_viewmode_$key"]=array(
                '#type'=>'checkbox',
                '#default_value'=>in_array($key,$config->get("viewmodes")),
                '#title'=>$viewmode['label'],
            );
        }

        $options=array();
        foreach($view_modes as $key=>$viewmode)
        {
            $options[$key]=$viewmode['label'];
        }

        $form['default_viewmode']=array(
            '#type'=>'select',
            '#title'=>'Default View Mode',
            '#options'=>$options,
            '#default_value'=>$config->get("default_viewmode"),
        );

        $form['blocks']=array(
            '#type'=>'fieldset',
            '#title'=>t('Supported Blocks'),
        );
        $blocks=array();
        $results=array();
        /** @var Block[] $blocks */
        $blocks=Block::loadMultiple();
        foreach($blocks as $idx=>$block)
        {
            $form['blocks']['grid_block_'.$block->id().'_enabled']=array(
                '#type'=>'checkbox',
                '#default_value'=>in_array($block->id(),$config->get("blocks")),
                '#title'=>$block->label()
            );
        }

        $form['imagestyles']=array(
            '#type'=>'fieldset',
            '#title'=>'Image style usage description'
        );
        $image_styles=ImageStyle::loadMultiple();
        foreach($image_styles as $key=>$style)
        {
            $form['imagestyles']['grid_imagestyle_'.$key.'_enabled']=array(
                '#type'=>'checkbox',
                '#default_value'=>in_array($style->id(),$config->get("imagestyles")),
                '#title'=>$key
            );
        }

        $form['use_grid_css']=array(
            '#type'=>'checkbox',
            '#title'=>t('use default Grid CSS'),
            '#default_value'=>$config->get('use_grid_css'),
        );

        $form['debug_mode']=array(
            '#type'=>'checkbox',
            '#title'=>t('Debug Mode'),
            '#default_value'=>$config->get('debug_mode'),
        );

        $form['async_service']=array(
            '#type'=>'fieldset',
            '#title'=>'Async services'
        );
        $form['async_service']['async_enabled']=array(
            '#type'=>'checkbox',
            '#default_value'=>$config->get('async_enabled'),
            '#title'=>'Enable',
        );

        $form['async_service']['async_url']=array(
            '#type'=>'textfield',
            '#title'=> 'Service URL (leave empty to use default service url)',
            '#default_value' => $config->get('async_url'),
        );

        return parent::buildForm($form, $form_state);
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $nodetypes=NodeType::loadMultiple();
        $keys=array_keys($nodetypes);
        $enabled_nodetypes=[];
        foreach($keys as $key)
        {
            $value=$form_state->getValue("grid_".$key."_enabled");
            if($value==1)
            {
                $enabled_nodetypes[]=$key;
            }
        }
        $viewmodes=\Drupal::entityManager()->getViewModes("node");
        $enabled_viewmodes=[];
        foreach($viewmodes as $key=>$viewmode)
        {
            if($form_state->getValue('grid_viewmode_'.$key)==1)
            {
                $enabled_viewmodes[]=$key;
            }
        }
        /** @var Block[] $blocks */
        $blocks=Block::loadMultiple();
        $enabled_blocks=[];
        foreach($blocks as $key=>$block)
        {
            if($form_state->getValue("grid_block_".$block->id()."_enabled")==1)
            {
                $enabled_blocks[]=$block->id();
            }
        }
        $image_styles=ImageStyle::loadMultiple();
        $enabled_imagestyles=array();
        foreach($image_styles as $key=>$style)
        {
            if($form_state->getValue("grid_imagestyle_".$style->id()."_enabled")==1)
            {
                $enabled_imagestyles[]=$style->id();
            }
        }

        $this->config('grid.settings')
            ->set('async_enabled',$form_state->getValue("async_enabled"))
            ->set('async_url',$form_state->getValue("async_url"))
            ->set('debug_mode',$form_state->getValue("debug_mode"))
            ->set('use_grid_css',$form_state->getValue('use_grid_css'))
            ->set('default_container',$form_state->getValue('default_container'))
            ->set('default_container_style',$form_state->getValue('default_container_style'))
            ->set('default_slot_style',$form_state->getValue('default_slot_style'))
            ->set('default_box_style',$form_state->getValue('default_box_style'))
            ->set('enabled_node_types',$enabled_nodetypes)
            ->set('viewmodes',$enabled_viewmodes)
            ->set('default_viewmode',$form_state->getValue('default_viewmode'))
            ->set('sidebar_content',$form_state->getValue('sidebar_content'))
            ->set('blocks',$enabled_blocks)
            ->set('imagestyles',$enabled_imagestyles)
            ->save();
        parent::submitForm($form, $form_state);
    }


}