<?php


namespace AppBundle\Common;


class PasswordGenerateToolkit
{
    private static $passwordDegree = ['low', 'middle', 'high'];

    public static function create($passwordType)
    {
        if (!in_array($passwordType, self::$passwordDegree)) {
            return false;
        }

        $method = "{$passwordType}Password";

        return self::$method();
    }

    private static function lowPassword()
    {
        $rule = [
            'number' => 1,
        ];
        return self::generate(6, $rule);
    }

    private static function middlePassword()
    {
        $rule = [
            'letter' => 3,
            'number' => 1,
        ];
        return self::generate(9, $rule);
    }

    private static function highPassword()
    {
        $rule = [
            'letter' => 2,
            'number' => 1,
            'special' => 1
        ];
        return self::generate(9, $rule);
    }

    private static function generate($length=8, $rule=array()){

        $pool = '';
        $force_pool = '';

        if(isset($rule['letter'])){

            $letter =self::getLetter();

            switch($rule['letter']){
                case 2:
                    $force_pool .= substr($letter, mt_rand(0,strlen($letter)-1), 1);
                    break;

                case 3:
                    $force_pool .= strtolower(substr($letter, mt_rand(0,strlen($letter)-1), 1));
                    $letter = strtolower($letter);
                    break;

                case 4:
                    $force_pool .= strtoupper(substr($letter, mt_rand(0,strlen($letter)-1), 1));
                    $letter = strtoupper($letter);
                    break;

                case 5:
                    $force_pool .= strtolower(substr($letter, mt_rand(0,strlen($letter)-1), 1));
                    $force_pool .= strtoupper(substr($letter, mt_rand(0,strlen($letter)-1), 1));
                    break;
                default:
                    break;
            }

            $pool .= $letter;

        }

        if(isset($rule['number'])){

            $number = self::getNumber();

            if ($rule['number'] == 1) {
                $force_pool .= substr($number, mt_rand(0,strlen($number)-1), 1);
            }

            $pool .= $number;

        }

        if(isset($rule['special'])){

            $special = self::getSpecial();

            if ($rule['special'] == 1) {
                $force_pool .= substr($special, mt_rand(0,strlen($special)-1), 1);
            }

            $pool .= $special;
        }

        $pool = str_shuffle($pool);

        return str_shuffle($force_pool. substr($pool, 0, $length-strlen($force_pool)));
    }


    private static function getLetter(){
        return 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';
    }


    private static function getNumber(){
        return '1234567890';
    }


    private static function getSpecial(){
        return  '!@#$%&*+=-';
    }
}