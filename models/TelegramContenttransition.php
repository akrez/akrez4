<?php

namespace app\models;

use Yii;
use yii\web\Response;

/**
 * This is the model class for table "telegram_contenttransition".
 *
 * @property int $id
 * @property int|null $created_at
 * @property string|null $forward_from
 * @property string|null $update_id
 * @property string|null $message
 */
class TelegramContenttransition extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'telegram_contenttransition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message'], 'string'],
            [['forward_from'], 'string', 'max' => 31],
            [['update_id'], 'string', 'max' => 15],
        ];
    }

    public static function hasPermission($blog)
    {
        return boolval($blog->telegram);
    }

    public static function response($message, $status = false, $data = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];
    }

    public static function send($token, $func, $postFields = [])
    {
        if (true) {
            $url = 'http://aliakbarrezaei.ir/telegram.php?func=' . $func . '&token=' . $token;
        } else {
            $url = 'https://api.telegram.org/bot' . $token . '/' . $func;
        }

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

    public static function updateTelegramContenttransition()
    {
        try {
            $response = self::send(Yii::$app->params['contentTransitionBotToken'], 'getUpdates');
            $response = (array) @json_decode($response, true);
            if (
                isset($response['ok']) && $response['ok'] &&
                isset($response['result']) && $response['result'] && is_array($response['result'])
            ) {
                foreach ($response['result'] as $message) {
                    $model = new TelegramContenttransition();
                    $model->message = json_encode($message);
                    $model->save();
                }
                return self::response('', true);
            }
        } catch (\Throwable $th) {
        }
        return self::response(Yii::t('yii', 'Error'));
    }

    public static function sendProductToChannel($blog, $product, $packageId = null)
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
                "media" => ($gallery->telegram_id ? $gallery->telegram_id : Gallery::getImageUrl(Gallery::TYPE_PRODUCT, $gallery->name, true)),
            ];
            if ($caption) {
                $medias[$gallery->name]['caption'] = implode("\n\n", $caption);
                $medias[$gallery->name]['parse_mode'] = 'html';
                $caption = null;
            }
        }

        $response = self::send($blog->telegram_bot_token, 'sendMediaGroup', [
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
                        if (isset($galleries[$galleryName]) && empty($galleries[$galleryName]->telegram_id)) {
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
