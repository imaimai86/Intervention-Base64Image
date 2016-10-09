<?php
namespace Base64Image;

use Intervention\Image\ImageManager;
use Intervention\Image\Exception as ImageException;

/**
 * Description of Image
 *
 * @author imaimai86
 */
class ImageHandler
{
	protected $imageSourceEncoded, $imageManager, $tempFilePath, $errors, $mimeType, $extension, $width, $height, $fileSize, $validationRules;
	protected $defaultValidations = 
			[
				'types' => [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG],
				'minFileSize' => 0,
				'maxFileSize' => 5000000,
			];
	protected $autoDelete = false;
	public $imageObject;
	
	public function __construct($fileContent, $validations = null)
	{
		try
		{		
			$this->imageSourceEncoded = $fileContent;
			$this->imageManager = new ImageManager();
			$this->imageObject = $this->imageManager->make($this->imageSourceEncoded);
			$this->fetchInfo();
			$this->validationRules = (null !== $validations && is_array($validations)) ? $validations : $this->defaultValidations;
		}
		catch (\Exception $ex)
		{
			throw new ImageException('Invalid image data');
		}
	}
	
	protected function fetchInfo()
	{
		$imageInfo = getimagesize($this->imageSourceEncoded);
		$this->mimeType = $imageInfo[2];
		$this->extension = image_type_to_extension($this->mimeType);
		$this->width = $imageInfo[0];
		$this->height = $imageInfo[1];
		$this->createTemporaryImage();
		$this->fileSize = filesize($this->tempFilePath);
	}

	public function isValidImage()
	{
		try
		{
			if (isset($this->validationRules['types']) && is_array($this->validationRules['types']))
			{
				if(!in_array($this->mimeType , $this->validationRules['types']))
				{
					$this->errors[] = 'Invalid image mime type';
				}
			}
			
			//Check size 
			if (isset($this->validationRules['minFileSize']) && is_numeric($this->validationRules['minFileSize']) && $this->fileSize <= $this->validationRules['minFileSize'])
			{
				$this->errors[] = 'File too small';
			}
			if (isset($this->validationRules['maxFileSize']) && is_numeric($this->validationRules['maxFileSize']) && $this->fileSize > $this->validationRules['maxFileSize'])
			{
				$this->errors[] = 'File too large';
			}
		}
		catch (\Exception $ex)
		{
			$this->errors[] = 'Invalid validation failed';
			return false;
		}
		
		return empty($this->errors);
	}

	protected function createTemporaryImage()
	{
		$tmp_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
		$temp_file = tempnam($tmp_dir, 'Img');
		$this->tempFilePath = $temp_file.$this->extension;
		$this->imageObject->save($this->tempFilePath, 100);
	}

	//Getters
	public function imageSourceEncoded(){	return $this->imageSourceEncoded;	} 
	public function imageManager(){	return $this->imageManager;	} 
	public function tempFilePath(){	return $this->tempFilePath;	} 
	public function errors(){	return $this->errors;	} 
	public function mimeType(){	return $this->mimeType;	} 
	public function extension(){	return $this->extension;	} 
	public function width(){	return $this->width;	} 
	public function height(){	return $this->height;	} 
	public function fileSize(){	return $this->fileSize;	}
	//Setters
	public function validation($data){	
		
		if (empty($data) || !is_array($data))
		{
			throw new ImageException('Invalid rules');
		}
		
		$this->validationRules = $data;
	}
	
	public function autoDelete($status = null)
	{
		if (null === $status) {	 return $this->autoDelete;	}
		$this->autoDelete = $status;
	}
	
	public static function cleanup($file)
	{
		if (is_array($file))
		{
			foreach ($file as $f)
			{
				if (!empty($f['path']))
				{
					self::removeFiles($f['path']);
				}
				else
				{
					self::removeFiles($file);
				}
			}
		}
		else
		{
			self::removeFiles($file);
		}
	}
	
	protected static function removeFiles($path = null)
	{
		if (!empty($path) && file_exists($path))
		{
			@unlink($path);
		}
	}

	public function __destruct()
	{
		if ($this->autoDelete())
		{
			$this->removeFiles($this->tempFilePath);
		}
	}
}
