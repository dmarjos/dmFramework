<?php
class imagesUtils {

	const PNG_FORMAT			= 1;
	const JPG_FORMAT			= 2;
	const GIF_FORMAT			= 3;
	
	private static $imageHolder		= null;
	private static $originalFormat  = null;
		
	public static function processImgHTML($html) {

		preg_match_all("/<img([^>]*)>/si",$html,$matches,PREG_SET_ORDER);
		if ($matches) {
			foreach($matches as $match) {
				$imgParams=self::getTagParameters($match[1]);
				$style="";
				if ($imgParams["style"]) {
					$css=self::processInlineCSS($imgParams["style"]);
					if ($css["float"])
						$style=" style=\"float: {$css["float"]};\"";
				}
				if ($imgParams["alt"]) 
					$html=str_replace($match[0],"<div{$style}>".$match[0]."<br/><div class=\"epigrafe\">".$imgParams["alt"]."</div></div>",$html);
				
			}
		}
		
		return $html;
		
	}
	
	public static function getTagParameters($strParameters) {
		preg_match_all("/([a-z\-_]*)=\"([^\"]*)\"/si",$strParameters,$matches,PREG_SET_ORDER);
		$parameters=array();
		if ($matches) {
			foreach($matches as $match) {
				$parameters[strtolower($match[1])]=$match[2];
			}
		}
		return $parameters;
	}

	public static function processInlineCSS($inlineCSS) {
		$inlineCSS=trim($inlineCSS);
		if (substr($inlineCSS,-1)!=";") $inlineCSS.=";";
		preg_match_all("/([a-z\-]*):([^;]*);/si",$inlineCSS,$matches,PREG_SET_ORDER);
		$css=array();
		if ($matches) {
			foreach ($matches as $match) {
				$css[strtolower($match[1])]=trim($match[2]);
			}
		}
		return $css;
	}
	
	public static function getScaledSize($image,$maxWidth,$maxHeight) {
		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$image)) {
			return array("width"=>0,"height"=>0);
		}
		$imageInfo=@getimagesize($_SERVER["DOCUMENT_ROOT"].$image);
		
		$width=$imageInfo[0];
		$height=$imageInfo[1];
		
		if ($width>$maxWidth) {
			$scale=$maxWidth/$width;
			$width=$maxWidth;
			$height=$imageInfo[1]*$scale;
		}
		if ($height>$maxHeight) {
			$scale=$maxHeight/$height;
			$height=$maxHeight;
			$width=$width*$scale;
		}
		return array("width"=>$width,"height"=>$height);
	}
	
	public static function getRemoteImage($url,$localFile,$width,$height) {
		$im=@imagecreatefromjpeg($url);
		if (!$im) $im=@imagecreatefrompng($url);
		if (!$im) $im=@imagecreatefromgif($url);

		if (is_dir(dirname($localFile))) {
			
		}
	}
	
	private static function loadImage($filename) {  
		
		$image_info = getimagesize($filename); 
		$image_type = $image_info[2];
		switch ($image_type) {
			case IMAGETYPE_JPEG:  
				self::$originalFormat=self::JPG_FORMAT; 
				self::$imageHolder = imagecreatefromjpeg($filename);
				break; 
			case IMAGETYPE_GIF:  
				self::$originalFormat=self::GIF_FORMAT; 
				self::$imageHolder = imagecreatefromgif($filename);
				break; 
			case IMAGETYPE_PNG:  
				self::$originalFormat=self::PNG_FORMAT; 
				self::$imageHolder = imagecreatefrompng($filename);
                imagealphablending(self::$imageHolder, false);
				break; 
		 }
	}
	
	private static function getWidth() {
		return imagesx(self::$imageHolder);
	}
	
	private static function getHeight() {
		return imagesy(self::$imageHolder);
	}
	
	private static function copyImage($dst_x,$dst_y,$src_x,$src_y,$src_w,$src_h,$dst_w=0,$dst_h=0) {
		$dst_w=ceil($dst_w);
		$dst_h=ceil($dst_h);
		$src_w=ceil($src_w);
		$src_h=ceil($src_h);
		if ($dst_w && $dst_h) {
			$dst=imagecreatetruecolor($dst_w,$dst_h);
		} else {
			$dst=imagecreatetruecolor($src_w,$src_h);
		}
		$white = imagecolorallocate($dst, 255, 255, 255);
		imagefill($dst,0,0,$white);
		imagecopy($dst,self::$imageHolder,$dst_x,$dst_y,$src_x,$src_y,$src_w,$src_h);
		$tmpName=sys_get_temp_dir()."/".md5(uniqid(time())).".png";
		@imagepng($dst,$tmpName);
		self::loadImage($tmpName);
		@unlink($tmpName);
		imagedestroy($dst);
		
	}
	
	private static function enlargePicture($maxWidth,$maxHeight) {
		$width=self::getWidth();
		$height=self::getHeight();
		
		if ($width<$maxWidth || $height<$maxHeight) {
			// una de las medidas es mayor o igual al maximo, la otra no. 
			// Escalamos para que el tamaño menor alcance el minimo requerido 
			if ($width<=$maxWidth) {
				// El ancho es muy chico, pero el alto esta perfecto.
				$scaleWidth=$maxWidth / $width;
				$newHeight=$height*$scaleWidth;
				$newWidth=$width*$scaleWidth;
			} else {
				// El alto es muy chico, pero el ancho esta perfecto.
				$scaleHeight=$maxHeight / $height;
				$newHeight=$height*$scaleHeight;
				$newWidth=$width*$scaleHeight;
			}
			$newImg=imagecreatetruecolor($newWidth,$newHeight);
			imagecopyresampled($newImg,self::$imageHolder,0,0,0,0,$newWidth,$newHeight,$width,$height);
			
			// Ahora tenemos una imagen temporal que tiene como minimo el acho o el alto requeridos
			$tmpName=sys_get_temp_dir()."/".md5(uniqid(time())).".png";
			@imagepng($newImg,$tmpName);
			self::loadImage($tmpName);
			@unlink($tmpName);
			imagedestroy($newImg);
		}
	} 
	
	private static function scaleHorizontalPicture($maxWidth,$maxHeight) {
		$width=self::getWidth();
		$height=self::getHeight();

		$scale=$maxWidth / $width;
		$newHeight=$height*$scale;
		$newWidth=$maxWidth;
		if ($newHeight<$maxHeight) {
			$scale=$maxHeight/$height;
			$newWidth=$width*$scale;
			$newHeight=$maxHeight;
		}
		// echo "imagen escalada a {$newWidth}x{$newHeight}<br/>";
		
		$newImg=imagecreatetruecolor($newWidth,$newHeight);
		imagecopyresampled($newImg,self::$imageHolder,0,0,0,0,$newWidth,$newHeight,$width,$height);
		
		// Ahora tenemos una imagen temporal que tiene como minimo el acho o el alto requeridos
		$tmpName=sys_get_temp_dir()."/".md5(uniqid(time())).".png";
		@imagepng($newImg,$tmpName);
		self::loadImage($tmpName);
		@unlink($tmpName);
		imagedestroy($newImg);
	}
	
	private static function scaleVerticalPicture($maxWidth,$maxHeight) {
		$width=self::getWidth();
		$height=self::getHeight();
		
		$scale=$maxHeight/$height;
		$newWidth=$width*$scale;
		$newHeight=$maxHeight;
		if ($newWidth<$maxWidth) {
			$scale=$maxWidth / $width;
			$newHeight=$height*$scale;
			$newWidth=$maxWidth;
		}
		
		$newImg=imagecreatetruecolor($newWidth,$newHeight);
		imagecopyresampled($newImg,self::$imageHolder,0,0,0,0,$newWidth,$newHeight,$width,$height);
		
		// Ahora tenemos una imagen temporal que tiene como minimo el acho o el alto requeridos
		$tmpName=sys_get_temp_dir()."/".md5(uniqid(time())).".png";
		@imagepng($newImg,$tmpName);
		self::loadImage($tmpName);
		@unlink($tmpName);
		imagedestroy($newImg);
	}
	
	public static function resizePicture($origFile,$destFile,$maxWidth,$maxHeight,$format=false) {
		self::loadImage($origFile);
		if (!$format) $format=self::$originalFormat;
		
		$width=self::getWidth();
		$height=self::getHeight();

		/*
		 Si la imagen que se espera es horizontal y se obtuvo una imagen vertical (o viceversa)
		 aseguramos que se pueda usar, escalando el alto o el ancho, dependiendo de la orientacion
		 de la imagen subida
		 */
		
		
		// echo "Ancho {$width} (Max Ancho: {$maxWidth})<br/>";
		// echo "Alto {$height} (Max Alto: {$maxHeight})<br/>";
		// la imagen es muy chica, no se puede hacer nada. Salimos con false.
		if($width<$maxWidth && $height<$maxHeight) {
			if ($maxWidth==179) {
				if ($width<130 && $height<130)
					return false;
			} else if ($maxWidth==1366) {
				if ($width<800 && $height<400)
					return false;
			}
		}
		/*
		 chequeo 1 de 3 posibilidades: 
		 a) Que la imagen esperada sea cuadrada, 
		 b) que la imagen esperada sea horizontal
		 c) que la imagen esperada sea vertical
		 */
		// 
		if ($maxWidth>$maxHeight) {
			// se espera una imagen horizontal
			if ($height>$width) {
				//si mandan una imagen vertical, afuera 
				// echo "Imagen rechazada. Se espera imagen horizontal, se recibe imagen vertical<br/>";
				return false;
			}
		} else if ($maxWidth<$maxHeight) {
			// se espera una imagen vertical
			if ($height<$width) {
				//si mandan una imagen horizontal, afuera
				// echo "Imagen rechazada. Se espera imagen vertical, se recibe imagen horizontal<br/>";
				return false;
			}
		} 
		
		self::enlargePicture($maxWidth,$maxHeight);
		$width=self::getWidth();
		$height=self::getHeight();
		
		/* 
		 -------------------------------------------------------------------------------
		 En este momento tenemos una imagen que tiene como minimo el tamaño esperado, 
		 puede ser cuadrada o alargada. Ahora puede darse solo 1 de 3 casos:
		 a) Que la imagen sea mayor en ambas dimensiones (ancho y alto)
		 b) Que la imagen sea mayor solo en el ancho (el alto seria correcto)
		 c) Que la imagen sea mayor solo en el alto  (al ancho seria el correcto)
		 -------------------------------------------------------------------------------
		*/
		// echo "<hr/>Despues de agrandar la imagen segun corresponda<br/>";
		
		// echo "Ancho {$width} (Max Ancho: {$maxWidth})<br/>";
		// echo "Alto {$height} (Max Alto: {$maxHeight})<br/>";
		
		if ($width > $maxWidth && $height > $maxHeight) {
			// Seria el caso A
			if ($width>=$height)
				self::scaleHorizontalPicture($maxWidth,$maxHeight);
			else if($width<$height) 
				self::scaleVerticalPicture($maxWidth,$maxHeight);
		} 
		$width=self::getWidth();
		$height=self::getHeight();
		
		// echo "<hr/>Despues de escalar la imagen segun corresponda<br/>";
		
		// echo "Ancho {$width} (Max Ancho: {$maxWidth})<br/>";
		// echo "Alto {$height} (Max Alto: {$maxHeight})<br/>";
		/*
		 Al llegar a este punto, eliminamos la opcion A, en la que la imagen es demasiado grande
		 En este punto, solo quedan como posibles las opciones B y C
		 */ 
		
		if ($width >= $maxWidth && $height == $maxHeight) {
			$centroImagen=($width/2);
			$anchoExcedente=($width-$maxWidth)/2;
			self::copyImage(0,0,$anchoExcedente,0,$maxWidth,$height);
				
			// Caso B
		} else if ($width == $maxWidth && $height >= $maxHeight) {
			// Caso C
			$centroImagen=($height/2);
			$altoExcedente=($height-$maxHeight)/2;
			// echo "Se recortan {$altoExcedente}px arriba y abajo<br/>";
			self::copyImage(0,0,0,$altoExcedente,$width,$maxHeight);
		}
		
		switch ($format) {
			case self::JPG_FORMAT:
				$retVal=imagejpeg(self::$imageHolder,$destFile);
				break;
			case self::GIF_FORMAT:
				$retVal=imagegif(self::$imageHolder,$destFile);
				break;
			case self::PNG_FORMAT:
			default:
				$retVal=imagepng(self::$imageHolder,$destFile);
				break;
		}
		return $retVal;
	}
	
	/*
	function resizePicture($origFile,$destFile,$maxWidth,$maxHeight,$format=false) {
		
		self::loadImage($origFile);
		$width=self::getWidth();
		$height=self::getHeight();

			// la imagen es muy chica, no se puede hacer nada. Salimos con false.
		if($width<$maxWidth && $height<$maxHeight) 
			return false; 
		
		// una de las medidas es mayor o igual al maximo, la otra no. 
		// Escalamos para que el tamaño menor alcance el minimo requerido 
		if ($width<$maxWidth || $height<$maxHeight) {
			if ($width<$maxWidth) {
				// El ancho es muy chico, pero el alto esta perfecto.
				$scaleWidth=$maxWidth / $width;
				$newHeight=$height*$scaleWidth;
				$newWidth=$width*scaleWidth;
			} else {
				// El alto es muy chico, pero el ancho esta perfecto.
				$scaleHeight=$maxHeight / $height;
				$newHeight=$height*$scaleHeight;
				$newWidth=$width*$scaleHeight;
			}
			$newImg=imagecreatetruecolor($newWidth,$newHeight);
			imagecopyresampled($newImg,self::$imageHolder,0,0,0,0,$newWidth,$newHeight,$width,$height);
			
			// Ahora tenemos una imagen temporal que tiene como minimo el acho o el alto requeridos
			$tmpName=sys_get_temp_dir()."/".md5(uniqid(time())).".png";
			@imagepng($dst,$tmpName);
			self::loadImage($tmpName);
			@unlink($tmpName);
			imagedestroy($newImg);
			$width=self::getWidth();
			$height=self::getHeight();
		}
		
		//if ($width<180 || $height<237) return false;
		
		if ($width<=$maxWidth && $height<=$maxHeight) {
			$dst=imagecreatetruecolor($width,$height);
			imagecopyresampled($dst,self::$imageHolder,0,0,0,0,$width,$height,self::getWidth(),self::getHeight());
			switch ($format) {
				case self::JPG_FORMAT:
					$retVal=@imagejpeg($dst,$destFile);
					break;
				case self::GIF_FORMAT:
					$retVal=@imagegif($dst,$destFile);
					break;
				case self::PNG_FORMAT:
				default:
					$retVal=@imagepng($dst,$destFile);
					break;
			}
			
			return $retVal;
		}
		if ($maxWidth==$maxHeight) {
			if ($width>$height ){
				$newWidth=$height;
				$xCenter=($width/2);
				$disposable=($width-$height)/2;
				self::copyImage(0,0,$disposable,0,$newWidth,$height);
				$width=self::getWidth();
				$height=self::getHeight();
			} else if ($width<$height){
				$newWidth=$height;
				$xCenter=($width/2);
				$disposable=($width-$height)/2;
				self::copyImage(0,0,$disposable,0,$newWidth,$height);
				$width=self::getWidth();
				$height=self::getHeight();
			}
		} else {
			if ($maxWidth==179 && $maxHeight==237) {
				if ($width>=$height){
					$newWidth=$height*0.755;
					$xCenter=($width/2);
					$dif=29*($height/$maxHeight);
					$disposable=(($width-$height)/2)+$dif;
					self::copyImage(0,0,$disposable,0,$newWidth,$height);
					$width=self::getWidth();
					$height=self::getHeight();
					error_log("Recalculado tamaño: Ancho: {$width}, Alto: {$height}.");
				} else if ($width<$height){
					$newHeight=$width*1.324;
					if ($newHeight>$height && $height>$maxHeight) {
						self::copyImage(0,0,0,0,$width,$height,$width,$maxHeight);
						$newHeight=$maxHeight;
					}
					$yCenter=($height/2);
					$disposable=($height-$width)/2;
					error_log("Medidas previas al recalculo: Ancho: {$width}, Alto: {$height}.");
					//error_log();
					self::copyImage(0,0,0,$disposable,$width,$newHeight,$newWidth,$height);
					$width=self::getWidth();
					$height=self::getHeight();
					error_log("Recalculado medidas: Ancho: {$width}, Alto: {$height}.");
				}
			}
		}		
		
		if ($width<=$maxWidth && $height<=$maxHeight) {
			error_log("Imagen resultante es mas chica que las medidas maximas");
			$dst=imagecreatetruecolor($width,$height);
			imagecopyresampled($dst,self::$imageHolder,0,0,0,0,$width,$height,self::getWidth(),self::getHeight());
		} else {
			if ($maxWidth>=$maxHeight) {
				$maxImageRatio=$maxWidth/$maxHeight;
				if ($width>=$heigth) {
					$uploadedImageRatio=$width/$height;
					if ($uploadedImageRatio!=$maxImageRatio) {
						$scale=$maxHeight/$height;
						$_height=$height;
						$_width=$height*$maxImageRatio;
						if ($_width<=$width) {
							$disposable=($width-$_width)/2;
							self::copyImage(0,0,$disposable,0,$_width,$height,$_width,$_height);
							$width=self::getWidth();
							$height=self::getHeight();
						} else {
							$_height=$width/$maxImageRatio;
							$disposable=floor(($height-$_height)/2);
							self::copyImage(0,0,0,$disposable,$width,$_height);
							$width=self::getWidth();
							$height=self::getHeight();
						}
					}
				} else {
					if ($width<=$maxWidth) {
						$_height=$width/$maxImageRatio;
						$disposable=floor(($height-$_height)/2);
						self::copyImage(0,0,0,$disposable,$width,$_height);
						$width=self::getWidth();
						$height=self::getHeight();
					} else {
						$_width=$maxWidth;
						$_height=$maxHeight;
						$yDisposable=floor(($height-$_height)/2);
						$xDisposable=floor(($width-$_width)/2);
						self::copyImage(0,0,$xDisposable,$yDisposable,$_width,$_height);
						$width=self::getWidth();
						$height=self::getHeight();
					}					
				}
			} else {
				$imageRatio=$maxHeight/$maxWidth;
			}
			
			if ($width<=$maxWidth && $height<=$maxHeight) {
				$dst=imagecreatetruecolor($width,$height);
				imagecopyresampled($dst,self::$imageHolder,0,0,0,0,$width,$height,self::getWidth(),self::getHeight());
			} else {
				error_log("La imagen resultante esta lista para ser escalada. Medidas actuales: Ancho: {$width}, Alto: {$height}.");
				if ($width>$maxWidth) {
					if ($height>$maxHeight) {
						$scale=$maxWidth/$width;
						$width=ceil($maxWidth);
						$height=ceil(self::getHeight()*$scale);
						error_log("Medidas recalculadas, Ancho: {$width}, Alto: {$height}.");
					} else {
						$_width=$maxWidth;
						$yDisposable=0;
						$xDisposable=floor(($width-$_width)/2);
						self::copyImage(0,0,$xDisposable,$yDisposable,$_width,$maxHeight);
						$width=self::getWidth();
						$height=self::getHeight();
						error_log("Medidas recalculadas, Ancho: {$width}, Alto: {$height}.");
					}
				}
				
				$dstY=$dstX=0;
				
				if ($height<=$maxHeight) {
					$dstY=($maxHeight-$height)/2;
				}
				
				if ($width<=$maxWidth) {
					$dstX=($maxWidth-$width)/2;
				}
				
				if ($maxHeight==237 && $height<237) return false;
				$dst=imagecreatetruecolor($maxWidth,$maxHeight);
				$white = imagecolorallocate($dst, 255, 255, 255);
				imagefill($dst,0,0,$white);
				$newImg=imagecopyresampled($dst,self::$imageHolder,$dstX,$dstY,0,0,$width,$height,self::getWidth(),self::getHeight());
				error_log("Generada la nueva imagen. Medidas: Ancho: {$width}, Alto: {$height}.");
				
			}
								
		}
		switch ($format) {
			case self::JPG_FORMAT:
				$retVal=imagejpeg($dst,$destFile);
				break;
			case self::GIF_FORMAT:
				$retVal=imagegif($dst,$destFile);
				break;
			case self::PNG_FORMAT:
			default:
				$retVal=imagepng($dst,$destFile);
				break;
		}
		
		return $retVal;
	}
	*/
}