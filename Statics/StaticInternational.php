<?php

namespace VisageFour\Bundle\ToolsBundle\Statics;


use App\Exceptions\CountryCodeDoesNotExist;
use App\Exceptions\LanguageCodeDoesNotExist;

/**
 * Created by PhpStorm.
 * User: CamBurns
 * Date: 12/10/2016
 * Time: 4:34 PM
 */

// folder: Statics contains static arrays that projects may want to use in different ways
class StaticInternational
{
    // todo: this should be phased out in preference for $residencyOptions2:
    public static $residencyOptions = array (
        'workVisa'      => 'Working Holiday',
        'studentVisa'   => 'Student Visa',
        'PR'            => 'Permanent Resident (PR)',
        'ausCitizen'    => 'Australian Citizen'
    );

    // visa types
    public static $residencyOptions2 = array(
        'WHV'   => "Working Holiday",
        'SV'    => "Student Visa",
        'PR'    => "Permanent Resident",
        'Cit'   => "Australian Citizen"
    );

    /**
     * note: please use getCountryNameByCode if you need a language name string
     */
    public static function getCountriesArray() {
        return self::$countries;
    }

    const EMPTY_CODE = 'ey';

    // countries
    // you must now use getCountryNameByCode() method to access these values
    private static $countries = array(
        self::EMPTY_CODE => 'Empty',            // could not find a physical flag (used for badges)
        'AF' => 'Afghanistan',
        'AX' => 'Aland Islands',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua And Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BA' => 'Bosnia And Herzegovina',
        'BW' => 'Botswana',
        'BV' => 'Bouvet Island',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean Territory',
        'BN' => 'Brunei Darussalam',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'CV' => 'Cape Verde',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos (Keeling) Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo',
        'CD' => 'Congo, Democratic Republic',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'CI' => 'Cote D\'Ivoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'EN' => 'England',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FK' => 'Falkland Islands (Malvinas)',
        'FO' => 'Faroe Islands',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French Southern Territories',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HM' => 'Heard Island & Mcdonald Islands',
        'VA' => 'Holy See (Vatican City State)',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran, Islamic Republic Of',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle Of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KS' => 'South Korea',
        'KO' => 'North Korea',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Lao People\'s Democratic Republic',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libyan Arab Jamahiriya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macao',
        'MK' => 'Macedonia',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia, Federated States Of',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'AN' => 'Netherlands Antilles',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PS' => 'Palestinian Territory, Occupied',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Reunion',
        'RO' => 'Romania',
        'RU' => 'Russian Federation',
        'RW' => 'Rwanda',
        'BL' => 'Saint Barthelemy',
        'SH' => 'Saint Helena',
        'KN' => 'Saint Kitts And Nevis',
        'LC' => 'Saint Lucia',
        'MF' => 'Saint Martin',
        'PM' => 'Saint Pierre And Miquelon',
        'VC' => 'Saint Vincent And Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome And Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SS' => 'Scotland',
        'SD' => 'Sudan',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia And Sandwich Isl.',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard And Jan Mayen',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad And Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks And Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'UM' => 'United States Outlying Islands',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela',
        'VN' => 'Viet Nam',
        'VG' => 'Virgin Islands, British',
        'VI' => 'Virgin Islands, U.S.',
        'WF' => 'Wallis And Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe'
    );

    /**
     * note: please use getLangByCode if you need a language name string
     */
    public static function getLanguages () {
        return self::$languages;
    }

    // languages - remember to use sort() to get inalphabetical order (if required)
    private static $languages = array (
        self::EMPTY_CODE => 'Empty',            // could not find a physical flag (used for badges)
        'aa' => 'Afar',
        'ab' => 'Abkhaz',
        'ae' => 'Avestan',
        'af' => 'Afrikaans',
        'ak' => 'Akan',
        'am' => 'Amharic',
        'an' => 'Aragonese',
        'ar' => 'Arabic',
        'as' => 'Assamese',
        'av' => 'Avaric',
        'ay' => 'Aymara',
        'az' => 'Azerbaijani',
        'ba' => 'Bashkir',
        'be' => 'Belarusian',
        'bg' => 'Bulgarian',
        'bh' => 'Bihari',
        'bi' => 'Bislama',
        'bm' => 'Bambara',
        'bn' => 'Bengali',
        'bo' => 'Tibetan Standard, Tibetan, Central',
        'bp' => 'Brazillian Portuguese',
        'br' => 'Breton',
        'bs' => 'Bosnian',
        'ca' => 'Catalan; Valencian',
        'cj' => 'Cantonese',
        'ce' => 'Chechen',
        'ch' => 'Chamorro',
        'cm' => 'Comorian',
        'co' => 'Corsican',
        'cr' => 'Cree',
        'cs' => 'Czech',
        'cu' => 'Old Church Slavonic, Church Slavic, Church Slavonic, Old Bulgarian, Old Slavonic',
        'cv' => 'Chuvash',
        'cy' => 'Welsh',
        'cz' => 'Chinese',
        'da' => 'Danish',
        'de' => 'German',
        'dv' => 'Divehi; Dhivehi; Maldivian;',
        'dz' => 'Dzongkha',
        'ee' => 'Ewe',
        'el' => 'Greek, Modern',
        'en' => 'English',
        'eo' => 'Esperanto',
        'es' => 'Spanish',       // todo: needs to be updated to 'Spanish' only
        'et' => 'Estonian',
        'eu' => 'Basque',
        'fa' => 'Persian',
        'ff' => 'Fula; Fulah; Pulaar; Pular',
        'fi' => 'Finnish',
        'fj' => 'Fijian',
        'fl' => 'Filipino',
        'fo' => 'Faroese',
        'fr' => 'French',
        'fy' => 'Western Frisian',
        'ga' => 'Irish',
        'gd' => 'Scottish Gaelic; Gaelic',
        'gl' => 'Galician',
        'gn' => 'GuaranÃ­',
        'gu' => 'Gujarati',
        'gv' => 'Manx',
        'ha' => 'Hausa',
        'he' => 'Hebrew (modern)',
        'hi' => 'Hindi',
        'ho' => 'Hiri Motu',
        'hr' => 'Croatian',
        'ht' => 'Haitian; Haitian Creole',
        'hu' => 'Hungarian',
        'hy' => 'Armenian',
        'hz' => 'Herero',
        'ia' => 'Interlingua',
        'id' => 'Indonesian',
        'ie' => 'Interlingue',
        'ig' => 'Igbo',
        'ii' => 'Nuosu',
        'ik' => 'Inupiaq',
        'io' => 'Ido',
        'is' => 'Icelandic',
        'it' => 'Italian',
        'iu' => 'Inuktitut',
        'ja' => 'Japanese',
        'jc' => 'Javanese',
        'ka' => 'Georgian',
        'kg' => 'Kongo',
        'ki' => 'Kikuyu, Gikuyu',
        'kj' => 'Kwanyama, Kuanyama',
        'kk' => 'Kazakh',
        'kl' => 'Kalaallisut, Greenlandic',
        'km' => 'Khmer',
        'kn' => 'Kannada',
        'ko' => 'Korean',
        'kr' => 'Kanuri',
        'ks' => 'Kashmiri',
        'ku' => 'Kurdish',
        'kv' => 'Komi',
        'kw' => 'Cornish',
        'ky' => 'Kirghiz, Kyrgyz',
        'la' => 'Latin',
        'lb' => 'Luxembourgish, Letzeburgesch',
        'lg' => 'Luganda',
        'li' => 'Limburgish, Limburgan, Limburger',
        'ln' => 'Lingala',
        'lo' => 'Lao',
        'lt' => 'Lithuanian',
        'lu' => 'Luba-Katanga',
        'lv' => 'Latvian',
        'mg' => 'Malagasy',
        'mh' => 'Marshallese',
        'mi' => 'Maori',
        'mk' => 'Macedonian',
        'ml' => 'Malayalam',
        'mn' => 'Mongolian',
        'mo' => 'Montenegrin',
        'mr' => 'Marathi (Marathi)',
        'ms' => 'Malay',
        'mt' => 'Maltese',
        'md' => 'Mandingo',                     // newly created.
        'my' => 'Burmese',
        'na' => 'Nauru',                        // todo: change this to Nauruan
        'nb' => 'Norwegian',            // remove this
        'nd' => 'North Ndebele',
        'ne' => 'Nepali',
        'ng' => 'Ndonga',
        'ni' => 'Niuean',                       // newly created
        'nl' => 'Dutch',
        'nn' => 'Norwegian Nynorsk',
        'no' => 'Norwegian',
        'nr' => 'South Ndebele',
        'nv' => 'Navajo, Navaho',
        'ny' => 'Chichewa; Chewa; Nyanja',
        'oc' => 'Occitan',
        'oj' => 'Ojibwe, Ojibwa',
        'om' => 'Oromo',
        'or' => 'Oriya',
        'os' => 'Ossetian, Ossetic',
        'pa' => 'Panjabi, Punjabi',
        'pi' => 'Pali',
        'pu' => 'Palauan',
        'pl' => 'Polish',
        'ps' => 'Pashto, Pushto',
        'pt' => 'Jamaican Patois',
        'pr' => 'Portuguese',
        'qu' => 'Quechua',
        'rm' => 'Romansh',
        'rn' => 'Kirundi',
        'ro' => 'Romanian, Moldavian, Moldovan',
        'ru' => 'Russian',
        'rw' => 'Kinyarwanda',
        'sa' => 'Sanskrit (Sa?sk?ta)',
        'sc' => 'Sardinian',
        'sd' => 'Sindhi',
        'se' => 'Northern Sami',
        'sh' => 'Sesotho',              // newly created
        'sg' => 'Sango',
        'si' => 'Sinhala, Sinhalese',
        'sk' => 'Slovak',
        'sl' => 'Slovene',
        'sm' => 'Samoan',
        'sn' => 'Shona',
        'so' => 'Somali',
        'sq' => 'Albanian',
        'sr' => 'Serbian',
        'ss' => 'Swati',
        'st' => 'Southern Sotho',
        'su' => 'Sundanese',
        'sv' => 'Swedish',
        'sw' => 'Swahili',
        'tz' => 'Taiwanese',
        'ta' => 'Tamil',
        'te' => 'Telugu',
        'tg' => 'Tajik',
        'th' => 'Thai',
        'ti' => 'Tigrinya',     // or: tigrigna
        'tk' => 'Turkmen',
        'tl' => 'Tagalog',
        'tn' => 'Tswana',
        'to' => 'Tonga (Tonga Islands)',            // todo: change this to tongan
        'tr' => 'Turkish',
        'ts' => 'Tsonga',
        'tt' => 'Tatar',
        'tw' => 'Twi',
        'ty' => 'Tahitian',
        'ug' => 'Uighur, Uyghur',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'uz' => 'Uzbek',
        've' => 'Venda',
        'vi' => 'Vietnamese',
        'vo' => 'VolapÃ¼k',
        'wa' => 'Walloon',
        'wo' => 'Wolof',
        'xh' => 'Xhosa',
        'yi' => 'Yiddish',
        'yo' => 'Yoruba',
        'za' => 'Zhuang, Chuang',
        'zu' => 'Zulu'
    );

    // a list of country keys that map to their default native language (uses array keys from other statics on this page.)
    // must now use: getDefaultNativeLanguage() method to access this array.
    public static $defaultNativeLanguage = array (
        StaticInternational::EMPTY_CODE => StaticInternational::EMPTY_CODE,
        'AF' => 'ps',           // Afghanistan          -> Pashto and dari
        'AX' => 'sv',           // Aland Islands        -> swedish
        'AL' => 'sq',           // Albania              -> Albanian
        'DZ' => 'ar',           // Algeria              -> arabic
        'AS' => 'sm',           // American Samoa       -> samoan
        'AD' => 'ca',           // Andorra              -> Catalan
        'AO' => 'pr',           // Angola               -> Portuguese
        'AI' => 'ht',           // Anguilla             -> Creole
        'AQ' => 'ru',           // Antarctica -> Russian
        'AG' => 'en',           // Antigua And Barbuda - > English
        'AR' => 'es',           // Argentina -> spanish
        'AM' => 'hy',           // Armenia -> Armenian
        'AW' => 'nl',           // Aruba -> Dutch
        'AU' => 'en',           // Australia -> English
        'AT' => 'de',           // Austria -> German and Slovenian
        'AZ' => 'az',           // Azerbaijan -> Azerbajani
        'BS' => 'en',           // Bahamas -> English
        'BH' => 'ar',           // Bahrain -> Arabic
        'BD' => 'bn',           // Bangladesh -> Bengali
        'BB' => 'en',           // arbados -> English
        'BY' => 'be',           // Belarus -> Belarusian
        'BE' => 'fr',           // Belgium -> French, Germna, Dutch
        'BZ' => 'en',           // Belize -> 'English'
        'BJ' => 'fr',           // Benin -> French
        'BM' => 'en',           // Bermuda -> English
        'BT' => 'dz',           // Bhutan -> Dzongkha
        'BO' => 'es',           // Bolivia
        'BA' => 'hr',           // Bosnia And Herzegovina -> Croatian
        'BW' => 'en',           // Botswana -> English
        'BV' => 'no',           // Bouvet Island -> Norwegian
        'BR' => 'bp',           // Brazil -> Brazillian Portuguese
        'IO' => 'en',           // British Indian Ocean Territory -> English
        'BN' => 'ms',           // Brunei Darussalam -> Malay
        'BG' => 'bg',           // Bulgaria -> Bulgarian
        'BF' => 'fr',           // Burkina Faso -> French
        'BI' => 'fr',           // Burundi -> French
        'KH' => 'km',           // Cambodia -> Khmer
        'CM' => 'fr',           // Cameroon -> French
        'CA' => 'en',           // Canada -> English, French
        'CV' => 'pr',           // Cape Verde -> Portuguese
        'KY' => 'en',           // Cayman Islands -> English
        'CF' => 'sg',           // Central African Republic -> Sango
        'TD' => 'fr',           // Chad -> French
        'CL' => 'es',           // Chile -> spanish
        'CN' => 'cz',           // China -> Mandarin
        'CX' => 'en',           // Christmas Island -> English
        'CC' => 'en',           // Cocos (Keeling) Islands -> English
        'CO' => 'es',           // Colombia -> Spanish
        'KM' => 'cm',           // Comoros -> Comorian
        'CG' => 'fr',           // Congo -> French
        'CD' => 'fr',           // Congo, Democratic Republic
        'CK' => 'mi',           // Cook Islands -> Maori
        'CR' => 'es',           // Costa Rica -> Spanish
        'CI' => 'fr',           // Cote De'Ivoire -> French
        'HR' => 'hr',           // Croatia -> Croatian
        'CU' => 'es',           // Cuba -> Spanish
        'CY' => 'tr',           // Cyprus -> Turkish
        'CZ' => 'cs',           // Czech Republic -> Czech
        'DK' => 'da',           // Denmark -> Danish
        'DJ' => 'so',           // Djibouti -> Somali
        'DM' => 'en',           // Dominica -> English
        'DO' => 'es',           // Dominican Republic -> Spanish
        'EC' => 'es',           // Ecuador -> Spanish
        'EG' => 'ar',           // Egypt -> Arabic
        'SV' => 'es',           // El Salvador -> Spanish
        'GQ' => 'es',           // Equatorial Guinea -> Equatorial Guinea
        'ER' => 'ti',           // Eritrea -> Tigrigna
        'EE' => 'et',           // Estonia -> Estonian
        'ET' => 'an',           // Ethiopia -> Amharic
        'EN' => 'en',           // England -> English
        'FK' => 'en',           // Falkland Islands (Malvinas) -> English
        'FO' => 'fo',           // Faroe Islands -> Faroese
        'FJ' => 'fj',           // Fiji -> Fijian
        'FI' => 'fi',           // Finland -> Finnish, Swedish
        'FR' => 'fr',           // France -> French
        'GF' => 'fr',           // French Guiana -> French
        'PF' => 'fr',           // French Polynesia -> French
        'TF' => 'fr',           // French Southern Territories -> French
        'GA' => 'fr',           // Gabon -> French
        'GM' => 'md',           // Gambia -> Mandingo
        'GE' => 'ka',           // Georgia -> Georgian
        'DE' => 'de',           // Germany -> German
        'GH' => 'ak',           // Ghana -> akan
        'GI' => 'es',           // Gibraltar -> c
        'GR' => 'el',           // Greece -> Greek
        'GL' => 'kl',           // Greenland -> Greenlandic
        'GD' => 'en',           // Grenada -> English
        'GP' => 'fr',           // Guadeloupe -> French
        'GU' => 'ch',           // Guam -> Chamorro
        'GT' => 'es',           // Guatemala -> Spanish
        'GG' => 'fr',           // Guernsey -> French
        'GN' => 'fr',           // Guinea -> French
        'GW' => 'pr',           // Guinea-Bissau -> Portuguese
        'GY' => 'en',           // Guyana -> English
        'HT' => 'ht',           // Haiti -> Haitian; Haitian Creole
        'HM' => 'en',           // Heard Island & Mcdonald Islands -> English
        'VA' => 'en',           // Holy See (Vatican City State) -> Italian
        'HN' => 'es',           // Honduras -> Spanish
        'HK' => 'cj',           // Hong Kong -> Cantonese, Mandarin, English
        'HU' => 'hu',           // Hungary -> Hungarian
        'IS' => 'is',           // Iceland -> Icelandic
        'IN' => 'hi',           // India -> Hindi
        'ID' => 'id',           // Indonesia -> Indonesian
        'IR' => 'fa',           // Iran, Islamic Republic Of -> Persian
        'IQ' => 'ar',           // Iraq -> Arabic
        'IE' => 'ga',           // Ireland -> Irish
        'IM' => 'gv',           // Isle Of Man -> Manx
        'IL' => 'he',           // Israel -> Hebrew
        'IT' => 'it',           // Italy -> Italian
        'JM' => 'ps',           // Jamaica -> Jamaican Patois
        'JP' => 'ja',           // Japan -> Japanese
        'JE' => 'fr',           // Jersey -> French
        'JO' => 'ar',           // Jordan -> Arabic
        'KZ' => 'kk',           // Kazakhstan -> kazakh
        'KE' => 'sw',           // Kenya -> Swahili
        'KI' => 'en',           // Kiribati -> English
        'KS' => 'ko',           // South Korea -> Korean
        'KO' => 'ko',           // South Korea -> Korean
        'KW' => 'ar',           // Kuwait -> Arabic
        'KG' => 'ky',           // Kyrgyzstan -> Kyrgyz
        'LA' => 'lo',           // Lao People's Democratic Republic -> Lao
        'LV' => 'lv',           // Latvia -> Latvian
        'LB' => 'ar',           // Lebanon -> Arabic
        'LS' => 'sh',           // Lesotho -> Sesotho
        'LR' => 'en',           // Liberia -> English
        'LY' => 'ar',           // Libyan Arab Jamahiriya -> Arabic
        'LI' => 'de',           // Liechtenstein -> German
        'LT' => 'lt',           // Lithuania -> Lithuanian
        'LU' => 'fr',           // Luxembourg -> French
        'MO' => 'cz',           // Macao -> Chinese
        'MK' => 'mk',           // Macedonia -> Macedonian
        'MG' => 'mg',           // Madagascar -> Malagasy
        'MW' => 'ny',           // Malawi -> Chewa
        'MY' => 'ms',           // Malaysia -> Malay
        'MV' => 'dv',           // Maldives -> Dhivehi
        'ML' => 'fr',           // Mali -> French
        'MT' => 'mt',           // Malta -> Maltese
        'MH' => 'mh',           // Marshall Islands -> Marshallese
        'MQ' => 'fr',           // Martinique -> French
        'MR' => 'ar',           // Mauritania -> Arabic
        'MU' => 'en',           // Mauritius -> English
        'YT' => 'fr',           // Mayotte -> French
        'MX' => 'es',           // Mexico -> Spanish
        'FM' => 'en',           // Micronesia, Federated States Of -> English
        'MD' => 'ro',           // Moldova -> Romanian
        'MC' => 'fr',           // Monaco -> French
        'MN' => 'mn',           // Mongolia -> Mongolian
        'ME' => 'mo',           // Montenegro -> Montenegrin
        'MS' => 'en',           // Montserrat -> English
        'MA' => 'ar',           // Morocco -> Arabic
        'MZ' => 'pr',           // Mozambique -> Portuguese
        'MM' => 'my',           // Myanmar -> Burmese
        'NA' => 'de',           // Namibia -> German
        'NR' => 'na',           // Nauru -> Nauruan
        'NP' => 'ne',           // Nepal -> Nepali
        'NL' => 'nl',           // Netherlands -> Dutch
        'AN' => 'en',           // Netherlands Antilles -> English
        'NC' => 'fr',           // New Caledonia -> French
        'NZ' => 'en',           // New Zealand -> English
        'NI' => 'es',           // Nicaragua -> spanish
        'NE' => 'fr',           // Niger -> French
        'NG' => 'ha',           // Nigeria -> Hausa
        'NU' => 'ni',           // Niue -> Niuean
        'NF' => 'en',           // Norfolk Island -> English
        'MP' => 'en',           // Northern Mariana Islands -> English
        'NO' => 'no',           // Norway -> Norwegian
        'OM' => 'ar',           // Oman -> Arabic
        'PK' => 'ur',           // Pakistan -> Urdu
        'PW' => 'pu',           // Palau -> Palauan
        'PS' => 'ar',           // Palestinian Territory, Occupied -> Arabic
        'PA' => 'es',           // Panama -> Spanish
        'PG' => 'en',           // Papua New Guinea -> English
        'PY' => 'es',           // Paraguay -> Spanish
        'PE' => 'es',           // Peru -> Spanish
        'PH' => 'fl',           // Philippines -> Filipino
        'PN' => 'en',           // Pitcairn -> English
        'PL' => 'pl',           // Poland -> Polish
        'PT' => 'pr',           // Portugal -> Portuguese
        'PR' => 'es',           // Puerto Rico -> Spanish
        'QA' => 'ar',           // Qatar -> Arabic
        'RE' => 'fr',           // Reunion -> French
        'RO' => 'ro',           // Romania -> Romanian
        'RU' => 'ru',           // Russian Federation -> Russian
        'RW' => 'sw',           // Rwanda -> swahili
        'BL' => 'fr',           // Saint Barthelemy
        'SH' => 'en',           // Saint Helena-> English
        'KN' => 'en',           // Saint Kitts And Nevis -> English
        'LC' => 'en',           // Saint Lucia -> english
        'MF' => 'nl',           // Saint Martin -> DUTCH
        'PM' => 'fr',           // Saint Pierre And Miquelon -> French
        'VC' => 'es',           // Saint Vincent And Grenadines -> Spanish
        'WS' => 'sm',           // Samoa -> Samoan
        'SM' => 'it',           // San Marino -> Italian
        'ST' => 'pr',           // Sao Tome And Principe -> Portuguese
        'SA' => 'ar',           // Saudi Arabia ->arabic
        'SN' => 'fr',           // Senegal -> French
        'RS' => 'sr',           // Serbia -> Serbian
        'SC' => 'fr',           // Seychelles -> French
        'SL' => 'bn',           // Sierra Leone -> Bengali
        'SG' => 'en',           // Singapore -> English
        'SK' => 'sk',           // Slovakia -> Slovak
        'SI' => 'sl',           // Slovenia -> Slovene
        'SB' => 'en',           // Solomon Islands -> English
        'SO' => 'so',           // Somalia -> Somali
        'ZA' => 'af',           // South Africa -> Afrikaans
        'GS' => 'en',           // South Georgia And Sandwich Isl. -> English
        'ES' => 'es',           // Spain -> Spanish
        'LK' => 'si',           // Sri Lanka -> Sinhala, English, Tamil
        'SD' => 'ar',           // Sudan -> Arabic
        'SR' => 'nl',           // Suriname -> Dutch
        'SJ' => 'no',           // Svalbard And Jan Mayen -> Norwegian
        'SZ' => 'ss',           // Swaziland -> Swati
        'SE' => 'sv',           // Sweden -> Swedish
        'SS' => 'en',           // Scotland -> English
        'CH' => 'fr',           // Switzerland -> French, Italian, German, Romansh
        'SY' => 'ar',           // Syrian Arab Republic -> Arabic
        'TW' => 'tz',           // Taiwan -> Taiwanese
        'TJ' => 'tg',           // Tajikistan -> Tajik
        'TZ' => 'sw',           // Tanzania -> Swahili
        'TH' => 'th',           // Thailand -> Thai
        'TL' => 'pr',           // Timor-Leste -> portuguese
        'TG' => 'fr',           // Togo -> French
        'TK' => 'en',           // Tokelau -> English
        'TO' => 'to',           // Tonga -> Tongan (Tonga Islands)
        'TT' => 'en',           // Trinidad And Tobago -> English
        'TN' => 'ar',           // Tunisia -> Arabic
        'TR' => 'tr',           // Turkey -> Turkish
        'TM' => 'tk',           // Turkmenistan -> Turkmen
        'TC' => 'en',           // Turks And Caicos Islands -> English
        'TV' => 'en',           // Tuvalu -> English
        'UG' => 'en',           // Uganda -> English, Swahili
        'UA' => 'uk',           // Ukraine -> Ukrainian
        'AE' => 'ar',           // United Arab Emirates -> Arabic
        'GB' => 'en',           // United Kingdom -> English
        'US' => 'en',           // United States -> English
        'UM' => 'en',           // United States Outlying Islands -> English
        'UY' => 'es',           // Uruguay -> Spanish
        'UZ' => 'uz',           // Uzbekistan -> Uzbek
        'VU' => 'fr',           // Vanuatu -> French
        'VE' => 'es',           // Venezuela -> Spanish
        'VN' => 'vi',           // Viet Nam -> Vietnamese
        'VG' => 'en',           // Virgin Islands, British -> English
        'VI' => 'en',           // Virgin Islands, U.S. -> English
        'WF' => 'fr',           // Wallis And Futuna -> French
        'EH' => 'ar',           // Western Sahara -> Arabic
        'YE' => 'ar',           // Yemen -> Arabic
        'ZM' => 'en',           // Zambia -> English
        'ZW' => 'en',           // Zimbabwe -> English
    );
    // todo: Australian states

    // todo: Victorian suburbs

    public static function getDefaultNativeLanguageCode (string $countryCode) : string {
        self::checkCountryExistsByCode($countryCode);

        if (empty(self::$defaultNativeLanguage[$countryCode])) {
            throw new \Exception('Country with code: '. $countryCode .' does not have a default language. Please set one.');
        }

        $defaultLang = self::$defaultNativeLanguage[$countryCode];

        return $defaultLang;
    }

    /**
     * @return string
     */
    public static function getCountryNameByCode($countryCode)
    {
        self::checkCountryExistsByCode($countryCode, true);

        return self::$countries[$countryCode];
    }

    public static function getLangNameByCode ($langCode) {
        self::checkLanguageExistsByCode($langCode, true);

        return self::$languages[$langCode];
    }

    /**
     * @param $code
     *
     * this will determine if it's a country or lang
     * and then return the full name of either
     *
     * note: for this to work, languages and country codes must be unique (i.e. a country code and a langauge code with the same value will result in a bug).
     */
    public static function getNameByCountryOrLanguageCode($code)
    {
        if (empty($code)) {
            return null;
        }
        try {
            $text = self::getLangNameByCode($code);
        } catch (LanguageCodeDoesNotExist $e) {
            // try matching with a country instead
            try {
                $text = self::getCountryNameByCode($code);
            } catch (CountryCodeDoesNotExist $e) {
                throw new \Exception ('code: '. $code .' is neither a language code or a country code. Please check the code and try again.');
            }
        }

        return $text;
    }

    public static function checkCountryExistsByCode ($countryCode, bool $throwOnError = true)
    {
        if (empty(self::$countries[$countryCode])) {
            if ($throwOnError) {
                throw new CountryCodeDoesNotExist($countryCode);
            }
            return false;
        }

        return true;
    }

    public static function checkLanguageExistsByCode (string $langCode, bool $throwOnError = true)
    {
        if (empty(self::$languages[$langCode])) {
            if ($throwOnError) {
                throw new LanguageCodeDoesNotExist($langCode);
            }

            return false;
        }

        return true;
    }

    /**
     * Check all $countries have a (valid) default language
     * throw an exception if a country does not have a default language set
     *
     * todo: turn this into a test
     */
    public static function checkCountriesHaveADefaultLanguage () {
        print "Checking countries have a (valid) language code. \n\n";
        foreach (self::$countries as $curCode => $curCountryName) {
            $defaultLangCode = self::getDefaultNativeLanguageCode($curCode);
//            print "- ". $curCountryName .' ('. $curCode ."): ". $defaultLangCode ."\n";
            if (empty($defaultLangCode)) {
                throw new \Exception(
                    'there is no default language code for country code: '. $curCode .
                    'please set this'
                );
            }

            // check if the code is a valid lang
            self::checkLanguageExistsByCode($defaultLangCode);
        }

//        print "[Finished check]\n\n";

        return true;
    }

    /**
     * @param $langCode
     * @return array
     *
     * return an array of country codes for countries that have $langCode
     * as it"s default language.
     */
    public static function getCountriesByDefaultLangCode($langCode)
    {
        // don't use array flip, as there may be multiple countries that use that language as their default.
//        $langs = array_flip($langs);

        // build an array of country codes that have $curLangCode as it's default language
        $countries = [];
        foreach (self::$defaultNativeLanguage as $curCountryCode => $curLangCode) {
            if ($langCode == $curLangCode) {
                array_push($countries, $curCountryCode);
            }
        }

        return $countries;
    }
}