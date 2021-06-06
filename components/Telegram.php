<?php

namespace app\components;

use app\models\Blog;
use app\models\Gallery;
use app\models\Package;
use app\models\Product;
use Exception;
use Yii;
use yii\base\Component;

class Telegram extends Component
{
    public static function hasPermission($blog)
    {
        return true;
    }

    public static function sendPackageToChannel(Package $package, $blog)
    {
        $product = $package->getProduct()->one();
        $caption = Product::printHtmlForTelegram($product);

        $params = [
            'chat_id' => '@' . $blog->telegram,
            'media' => json_encode([
                [
                    "type" => "photo",
                    "media" => $product->image ? Gallery::getImageUrl(Gallery::TYPE_PRODUCT, $product->image, true) : Blog::getLogoUrl(),
                    'parse_mode' => 'html',
                    'caption' => implode("\n", $caption),
                ],
            ]),
        ];
        $caption[] = '<b>' . Yii::$app->formatter->asPrice($package->price) . '</b>';

        $curl = curl_init('https://api.telegram.org/bot' . $blog->telegram_bot_token . '/sendMediaGroup');

        curl_setopt_array($curl,  [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $params,
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }
}
