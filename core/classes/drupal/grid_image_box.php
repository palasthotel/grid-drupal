<?php

use Drupal\Core\Image\ImageFactory;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

class grid_image_box extends grid_static_base_box
{
	public function __construct()
	{
		parent::__construct();
		$this->content->fileid="";
		$this->content->url = "";
		$this->content->imagestyle = "";
	}

	public function type() {
		return 'image';
	}

	/**
	 * @param bool $editmode
	 * @return string
     */
	public function build($editmode) {
		//boxes render their content in here
		if(isset($this->content->fileid) && $this->content->fileid!="")
		{
			/** @var File $file */
			$file=File::load($this->content->fileid);

			$a_pre = "";
			$a_post = "";
			if(isset($this->content->url) && $this->content->url != ""){
				$a_pre = '<a href="'.$this->content->url.'">';
				$a_post = '</a>';
			}
			if($editmode)
			{
				$a_post.=" (".$file->getFilename().")";
			}

			$src = "no_file";
			$width_html = '';
			$height_html = '';

			if(is_object($file)){
				if(
					isset($this->content->imagestyle) && 
					$this->content->imagestyle != "" &&
					array_key_exists($this->content->imagestyle, grid_image_styles())
				){
					// KM use drupal api to generate html output
					// @todo individual alt tag
					$input=array(
						'#theme'=>'image_style',
						'#style_name' => $this->content->imagestyle,
						'#alt' => '',
						'#uri' => $file->getFileUri(),
						'width' => null,
						'height' => null,
						'#attributes' => array(
							'class' => array('grid-box-image-img'),
						),
					);
					$image_html = \Drupal::service('renderer')->render($input);
					return $a_pre . $image_html . $a_post;
				}
				$src = file_create_url($file->getFileUri());
				/** @var ImageFactory $factory */
				$factory=\Drupal::service("image.factory");
				$image = $factory->get($file->getFileUri());
				$width_html = ($image->getWidth()!=NULL)? ' width="' . $image->getWidth() . '"' : "";
				$height_html = ($image->getHeight()!=NULL)? ' height="' . $image->getHeight() . '"' : "";
			}
			return $a_pre."<img class='grid-box-image-img' src='".$src."' alt=''" . $width_html . $height_html . " />".$a_post;
		}
		return t('Imagebox');
	}


	public function contentStructure () {
		$styles = array(
				array("text" => "- ".t("Original")." -", "key" => ""),
			);
		/**
		 * @var string $key
		 * @var ImageStyle $style
		 */
		foreach (grid_image_styles() as $key => $style) {
			$styles[] = array("text" => (empty($style->label()) ? $key : $style->label()), "key" => $key );
		}
		return array(
			array(
				'key'=>'fileid',
				'type'=>'file',
				'label'=>t('Image'),
				'uploadpath'=>'/grid_file_endpoint',
			),
			array(
				'key' => 'url',
				'type' => 'text',
				'label' => t('URL (optional)'),
			),
			array(
				'key' => 'imagestyle',
				'type' => 'select',
				'label' => t('Image style'),
				'selections'=>$styles,
				'info' => nl2br(""),
			),
		);
	}

	public function delete() {
		if($this->content->fileid!="")
		{
			//$file=file_load($this->content->fileid);
			//file_delete($file);
		}
		parent::delete();
	}


	public function performFileUpload($key,$path,$original_file)
	{
		if($key!='fileid')
			return FALSE;//array('result'=>FALSE,'error'=>'wrong key');
		$content=file_get_contents($path);
		$filename=basename($path);
		$path="public://grid/".date("Y/m/d")."/";
    /** @var \Drupal\Core\File\FileSystemInterface $filesystem */
    $filesystem=\Drupal::service('file_system');
    $filesystem->prepareDirectory($path,\Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);
		/** @var File $file */
		$file=file_save_data($content,$path.$original_file);
		return $file->id();
	}

	public function prepareReuseDeletion()
	{
		$this->content->fileid="";
	}


}
