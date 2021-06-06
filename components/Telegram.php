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
        return boolval($blog->telegram);
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

        $caption = [
            'type' => 'photo',
            'parse_mode' => 'html',
            'caption' => implode("\n\n", $caption),
        ];

        if ($product->image) {
            $caption['media'] = Gallery::getImageUrl(Gallery::TYPE_PRODUCT, $product->image, true);
            $medias = [$product->image => $caption];
        } else {
            $caption['media'] = Blog::getLogoUrl(true);
            $medias = [Blog::print('logo') => $caption];
        }

        $galleries = Gallery::findProductGalleryQueryForApi($blog->name, $product->id)->indexBy('name')->all();
        if (isset($galleries[$product->image])) {
            unset($galleries[$product->image]);
        }
        foreach ($galleries as $gallery) {
            $medias[$gallery->name] =  [
                "type" => "photo",
                "media" => Gallery::getImageUrl(Gallery::TYPE_PRODUCT, $gallery->name, true),
            ];
        }

        $curl = curl_init('https://api.telegram.org/bot' . $blog->telegram_bot_token . '/sendMediaGroup');
        curl_setopt_array($curl,  [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'chat_id' => '@' . $blog->telegram,
                'media' => json_encode(array_values($medias)),
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;
    }
}
