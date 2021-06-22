<?php

namespace app\components;

use Exception;
use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Throwable;
use Yii;
use yii\base\Component;
use yii\imagine\Image as Imagine;

class Image extends Component
{
    const MODE_NONE = 0;
    const MODE_SQUARE_INBOUND = 1;
    const MODE_SQUARE_OUTBOUND = 2;

    public static $validTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];
    public static function getValidModes()
    {
        return [
            self::MODE_NONE => Yii::t('app', 'image_mode_none'),
            self::MODE_SQUARE_INBOUND => Yii::t('app', 'image_mode_square_inbound'),
            self::MODE_SQUARE_OUTBOUND => Yii::t('app', 'image_mode_square_outbound'),
        ];
    }
    //
    public $basePath = ".";
    //
    private $_info;
    private $_error;

    public function getInfo()
    {
        return $this->_info;
    }

    public function getError()
    {
        return $this->_error;
    }

    public function save($srcFile, $des, $options = [])
    {
        try {

            $options = $options + [
                'width' => null,
                'height' => null,
                'quality' => null,
                'desIsAbsolute' => false,
                'mode' => 0,
            ];
            $width = $options['width'];
            $height = $options['height'];
            $quality = $options['quality'];
            $desIsAbsolute = $options['desIsAbsolute'];
            $mode = $options['mode'];

            $this->setError(null);

            if (!file_exists($srcFile)) {
                return $this->setError(1);
            }

            $imageSize = getimagesize($srcFile);
            if (!$imageSize) {
                return $this->setError(2);
            }

            $mime = $imageSize['mime'];

            $ext = null;
            if (array_key_exists($mime, self::$validTypes)) {
                $ext = self::$validTypes[$mime];
            } else {
                return $this->setError(3);
            }

            /*
             *   SAVE PART
             */

            $width = (empty($width) || $width < 1 || $imageSize[0] * 3 < $width ? null : intval($width));
            $height = (empty($height) || $height < 1 || $imageSize[1] * 3 < $height ? null : intval($height));
            $quality = (empty($quality) || $quality < 1 || 100 < $quality ? null : intval($quality));
            $mode = (empty($mode) || !in_array($mode, array_keys(Image::getValidModes())) ? 0 : intval($mode));

            $image = Imagine::getImagine()->open($srcFile);

            if ($width && $height) {
            } elseif ($width) {
                $height = ($width * $imageSize[1]) / $imageSize[0];
            } elseif ($height) {
                $width = ($height * $imageSize[0]) / $imageSize[1];
            } else {
                $width = $imageSize[0];
                $height = $imageSize[1];
            }
            if ($mode == 1) {
                if ($width > $height) {
                    $size = $width;
                } else {
                    $size = $height;
                }
                $image = Imagine::resize($image, $size, $size, true, true);
                $box = new Box($size, $size);
                //
                if ($imageSize[0] / $size < $imageSize[1] / $size) {
                    $scale = $size / $imageSize[1];
                } else {
                    $scale = $size / $imageSize[0];
                }
                $newWidth = intval($imageSize[0] * $scale);
                $newHeight = intval($imageSize[1] * $scale);
                //
                $color = (new RGB())->color('FFF', 0);
                $pasteTo = new Point(($size - $newWidth) / 2, ($size - $newHeight) / 2);
                //
                $image = Imagine::getImagine()->create($box, $color)->paste($image, $pasteTo);
            } elseif ($mode == 2) {
                if ($width > $height) {
                    $size = $height;
                } else {
                    $size = $width;
                }
                $image = Imagine::thumbnail($image, $size, $size);
            } else {
                $image = Imagine::resize($image, $width, $height, false, true);
            }

            if ($desIsAbsolute) {
                $pathinfo = pathinfo($des);
                $name = $pathinfo['basename'];
                $desFile = $des;
            } else {
                do {
                    $name = substr(uniqid(rand(), true), 0, 12) . '.' . $ext;
                    $desFile = $des . '/' . $name;
                } while (file_exists($desFile));
            }

            $image->save($desFile, ['quality' => $quality]);

            if (file_exists($desFile)) {
                $desSize = $image->getSize();
                return $this->setError(null, [
                    'desWidth' => $desSize->getWidth(),
                    'desHeight' => $desSize->getHeight(),
                    'desName' => $name,
                    'desFile' => $desFile,
                ]);
            }
        } catch (Throwable $e) {
        } catch (Exception $e) {
        }

        return $this->setError(-1);
    }

    private function setError($code, $info = null)
    {
        if ($code === null) {
            $this->_error = null;
        } elseif ($code == 1) {
            $this->_error = Yii::t('yii', 'Please upload a file.');
        } elseif ($code == 2) {
            $this->_error = Yii::t('yii', 'Only files with these extensions are allowed: {extensions}.', ['extensions' => implode(', ', self::$validTypes)]);
        } elseif ($code == 3) {
            $this->_error = Yii::t('yii', 'Only files with these extensions are allowed: {extensions}.', ['extensions' => implode(', ', self::$validTypes)]);
        } else {
            $this->_error = Yii::t('yii', 'Error');
        }

        return $this->_info = $info;
    }
}
