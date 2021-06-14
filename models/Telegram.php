<?php

namespace app\models;

use Yii;
use yii\web\Response;

/**
 * This is the model class for table "telegram".
 *
 * @property int $id
 * @property int|null $created_at
 * @property string|null $update_id
 * @property string|null $message
 * @property string|null $blog_name
 */
class Telegram extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'telegram';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message'], 'string'],
            [['update_id'], 'string', 'max' => 15],
            [['blog_name'], 'string', 'max' => 31],
        ];
    }

    public static function hasPermission($blog)
    {
        return boolval($blog->telegram);
    }

    public static function response($message, $status = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => $status,
            'message' => $message,
        ];
    }

    public static function send($url, $postFields)
    {
        $curl = curl_init($url);
        curl_setopt_array($curl,  [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public static function sendProductToChannel($blog, $product, $packageId)
    {
        $caption = [Product::printHtmlForTelegram($product, "\n")];

        if ($packageId == -1) {
            $packages = [];
        } else {
            $packages = Package::findProductPackageQueryForApi($blog->name, $product->id)->andFilterWhere(['id' => $packageId])->all();
        }
        foreach ($packages as $package) {
            $caption[] = Package::printHtmlForTelegram($package, "\n");
        }

        $galleries = Gallery::findProductGalleryQueryForApi($blog->name, $product->id)->indexBy('name')->all();
        if (empty($galleries)) {
            return self::response(Yii::t('yii', 'Please upload a file.'));
        }

        if (isset($galleries[$product->image])) {
            $galleries = [$product->image => $galleries[$product->image]] + $galleries;
        }
        foreach ($galleries as $gallery) {
            $medias[$gallery->name] =  [
                "type" => "photo",
                "media" => ($gallery['telegram_id'] ? $gallery['telegram_id'] : Gallery::getImageUrl(Gallery::TYPE_PRODUCT, $gallery->name, true)),
            ];
            if ($caption) {
                $medias[$gallery->name]['caption'] = implode("\n\n", $caption);
                $medias[$gallery->name]['parse_mode'] = 'html';
                $caption = null;
            }
        }

        $response = self::send('https://api.telegram.org/bot' . $blog->telegram_bot_token . '/sendMediaGroup', [
            'chat_id' => '@' . $blog->telegram,
            'media' => json_encode(array_values($medias)),
        ]);

        try {
            if ($response) {
                $response = json_decode($response, true);
                if (
                    isset($response['ok']) && $response['ok'] &&
                    isset($response['result']) && is_array($response['result']) && $response['result']
                ) {
                    $i = 0;
                    foreach ($medias as $galleryName => $media) {
                        $photo = end($response['result'][$i]['photo']);
                        if (isset($galleries[$galleryName]) && empty($galleries[$galleryName]['telegram_id'])) {
                            $galleries[$galleryName]->updateTelegramId($photo['file_id']);
                        }
                        $i++;
                    }
                    return self::response('', true);
                }
            }
        } catch (\Throwable $th) {
        }
        return self::response(Yii::t('yii', 'Error'));
    }
}
