<?php

use Drupal\Core\Image\ImageFactory;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

class grid_gallery_box extends grid_static_base_box
{
	public function __construct()
	{
		parent::__construct();
		$this->content->images = array();
	}

	public function type() {
		return 'gallery';
	}

	public function contentStructure()
	{
		$cs = parent::contentStructure();

		return array_merge($cs,
			array(
				array(
					'key'=>'images',
					'type'=>'list',
					'label'=>t('Images'),
					'contentstructure' => array(
						array(
							"key"=>"image",
							"label"=> t("Image"),
							"type" => "file",
							"uploadpath" => "/grid_file_endpoint",
						),
						array(
							"key"=>"description",
							"label"=> t("Description (optional)"),
							"type" => "text",
						),
						array(
							"key"=>"link",
							"label"=> t("Link (optional)"),
							"type" => "text",
						)
					),
				),
			)
		);
	}

	public function performFileUpload($key,$path, $original_file){
		if($key!="images.image")
			return FALSE;//array('result'=>FALSE,'error'=>'wrong box');

		$content=file_get_contents($path);
		$filename=basename($path);
		$path="public://grid/".date("Y/m/d")."/";
		file_prepare_directory($path,FILE_CREATE_DIRECTORY);
		/** @var File $file */
		$file=file_save_data($content,$path.$original_file);

		return $file->id();
	}

	public function build($editmode)
	{
		if($editmode)
		{
			if($this->grid != null && count($this->content->images) > 0 ){
				ob_start();

				echo "<div style='display:flex'>";
				foreach ($this->content->images as $item){

					$fid = $item->image;
					$file= \Drupal\file\Entity\File::load($fid);
					$src = file_create_url($file->getFileUri());

					?>
					<div style="padding: 5px; max-width: 150px;">
						<img src="<?php echo $src; ?>" style="max-width: 100px;" /><br>
						<span style="font-style: italic"><?php echo $item->description; ?> <?php if(!empty($item->link)){ echo "🔗 ".$item->link; } ?> </span>
					</div>
					<?php

				}
				echo "</div>";

				$output = ob_get_contents();
				ob_end_clean();

				return $output;

			}


			return "Gallery";
		}
		else
		{
			$gallery = array();
			foreach ($this->content->images as $item){

				$fid = $item->image;
				$file= \Drupal\file\Entity\File::load($fid);
				$src = file_create_url($file->getFileUri());

				$gallery[] = (object)array(
					"src" => $src,
					"description" => $item->description,
					"link" => $item->link,
				);

			}

			return $gallery;
		}
	}

}