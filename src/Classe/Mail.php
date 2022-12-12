<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
        private $api_key = 'ddac7f3be62e3259f25f5a21baa950ba';
        private $api_key_secret = '31a23f13b66f35647208aca0257c8a4f';

        public function send($to_email, $to_name, $subject, $content){
            $mj = new Client($this->api_key, $this->api_key_secret,true,['version' => 'v3.1']);
            // Ligne généré par Mailjet
            //$mj = new \Mailjet\Client(getenv('MJ_APIKEY_PUBLIC'), getenv('MJ_APIKEY_PRIVATE'),);
            $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => "kaedusmayer@outlook.fr",
                            'Name' => "MBZH"
                        ],
                        'To' => [
                            [
                                'Email' => $to_email,
                                'Name' => $to_name,
                            ]
                        ],
                        'TemplateID' => 4406870,
                        'TemplateLanguage' => true,
                        'Subject' => $subject,
                        'Variables' => [
                            'content' => $content,
                        ]
                    ]
                ]
            ];
            $response = $mj->post(Resources::$Email, ['body' => $body]);
            $response->success() && $response->getData();
        }
}