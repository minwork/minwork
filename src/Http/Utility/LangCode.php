<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Utility;

/**
 * Language codes container
 *
 * @author Christopher Kalkhoff
 *        
 */
class LangCode
{

    const ENGLISH = "en";

    const AFAR = "aa";

    const ABKHAZIAN = "ab";

    const AFRIKAANS = "af";

    const AMHARIC = "am";

    const ARABIC = "ar";

    const ASSAMESE = "as";

    const AYMARA = "ay";

    const AZERBAIJANI = "az";

    const BASHKIR = "ba";

    const BYELORUSSIAN = "be";

    const BULGARIAN = "bg";

    const BIHARI = "bh";

    const BISLAMA = "bi";

    const BENGALI_BANGLA = "bn";

    const TIBETAN = "bo";

    const BRETON = "br";

    const CATALAN = "ca";

    const CORSICAN = "co";

    const CZECH = "cs";

    const WELSH = "cy";

    const DANISH = "da";

    const GERMAN = "de";

    const BHUTANI = "dz";

    const GREEK = "el";

    const ESPERANTO = "eo";

    const SPANISH = "es";

    const ESTONIAN = "et";

    const BASQUE = "eu";

    const PERSIAN = "fa";

    const FINNISH = "fi";

    const FIJI = "fj";

    const FAEROESE = "fo";

    const FRENCH = "fr";

    const FRISIAN = "fy";

    const IRISH = "ga";

    const SCOTS_GAELIC = "gd";

    const GALICIAN = "gl";

    const GUARANI = "gn";

    const GUJARATI = "gu";

    const HAUSA = "ha";

    const HINDI = "hi";

    const CROATIAN = "hr";

    const HUNGARIAN = "hu";

    const ARMENIAN = "hy";

    const INTERLINGUA = "ia";

    const INTERLINGUE = "ie";

    const INUPIAK = "ik";

    const INDONESIAN = "in";

    const ICELANDIC = "is";

    const ITALIAN = "it";

    const HEBREW = "iw";

    const JAPANESE = "ja";

    const YIDDISH = "ji";

    const JAVANESE = "jw";

    const GEORGIAN = "ka";

    const KAZAKH = "kk";

    const GREENLANDIC = "kl";

    const CAMBODIAN = "km";

    const KANNADA = "kn";

    const KOREAN = "ko";

    const KASHMIRI = "ks";

    const KURDISH = "ku";

    const KIRGHIZ = "ky";

    const LATIN = "la";

    const LINGALA = "ln";

    const LAOTHIAN = "lo";

    const LITHUANIAN = "lt";

    const LATVIAN_LETTISH = "lv";

    const MALAGASY = "mg";

    const MAORI = "mi";

    const MACEDONIAN = "mk";

    const MALAYALAM = "ml";

    const MONGOLIAN = "mn";

    const MOLDAVIAN = "mo";

    const MARATHI = "mr";

    const MALAY = "ms";

    const MALTESE = "mt";

    const BURMESE = "my";

    const NAURU = "na";

    const NEPALI = "ne";

    const DUTCH = "nl";

    const NORWEGIAN = "no";

    const OCCITAN = "oc";

    const OROMOOR_ORIYA = "om";

    const PUNJABI = "pa";

    const POLISH = "pl";

    const PASHTO_PUSHTO = "ps";

    const PORTUGUESE = "pt";

    const QUECHUA = "qu";

    const RHAETO_ROMANCE = "rm";

    const KIRUNDI = "rn";

    const ROMANIAN = "ro";

    const RUSSIAN = "ru";

    const KINYARWANDA = "rw";

    const SANSKRIT = "sa";

    const SINDHI = "sd";

    const SANGRO = "sg";

    const SERBO_CROATIAN = "sh";

    const SINGHALESE = "si";

    const SLOVAK = "sk";

    const SLOVENIAN = "sl";

    const SAMOAN = "sm";

    const SHONA = "sn";

    const SOMALI = "so";

    const ALBANIAN = "sq";

    const SERBIAN = "sr";

    const SISWATI = "ss";

    const SESOTHO = "st";

    const SUNDANESE = "su";

    const SWEDISH = "sv";

    const SWAHILI = "sw";

    const TAMIL = "ta";

    const TEGULU = "te";

    const TAJIK = "tg";

    const THAI = "th";

    const TIGRINYA = "ti";

    const TURKMEN = "tk";

    const TAGALOG = "tl";

    const SETSWANA = "tn";

    const TONGA = "to";

    const TURKISH = "tr";

    const TSONGA = "ts";

    const TATAR = "tt";

    const TWI = "tw";

    const UKRAINIAN = "uk";

    const URDU = "ur";

    const UZBEK = "uz";

    const VIETNAMESE = "vi";

    const VOLAPUK = "vo";

    const WOLOF = "wo";

    const XHOSA = "xh";

    const YORUBA = "yo";

    const CHINESE = "zh";

    const ZULU = "zu";

    const CODES_LIST = [
        self::ENGLISH,
        self::AFAR,
        self::ABKHAZIAN,
        self::AFRIKAANS,
        self::AMHARIC,
        self::ARABIC,
        self::ASSAMESE,
        self::AYMARA,
        self::AZERBAIJANI,
        self::BASHKIR,
        self::BYELORUSSIAN,
        self::BULGARIAN,
        self::BIHARI,
        self::BISLAMA,
        self::BENGALI_BANGLA,
        self::TIBETAN,
        self::BRETON,
        self::CATALAN,
        self::CORSICAN,
        self::CZECH,
        self::WELSH,
        self::DANISH,
        self::GERMAN,
        self::BHUTANI,
        self::GREEK,
        self::ESPERANTO,
        self::SPANISH,
        self::ESTONIAN,
        self::BASQUE,
        self::PERSIAN,
        self::FINNISH,
        self::FIJI,
        self::FAEROESE,
        self::FRENCH,
        self::FRISIAN,
        self::IRISH,
        self::SCOTS_GAELIC,
        self::GALICIAN,
        self::GUARANI,
        self::GUJARATI,
        self::HAUSA,
        self::HINDI,
        self::CROATIAN,
        self::HUNGARIAN,
        self::ARMENIAN,
        self::INTERLINGUA,
        self::INTERLINGUE,
        self::INUPIAK,
        self::INDONESIAN,
        self::ICELANDIC,
        self::ITALIAN,
        self::HEBREW,
        self::JAPANESE,
        self::YIDDISH,
        self::JAVANESE,
        self::GEORGIAN,
        self::KAZAKH,
        self::GREENLANDIC,
        self::CAMBODIAN,
        self::KANNADA,
        self::KOREAN,
        self::KASHMIRI,
        self::KURDISH,
        self::KIRGHIZ,
        self::LATIN,
        self::LINGALA,
        self::LAOTHIAN,
        self::LITHUANIAN,
        self::LATVIAN_LETTISH,
        self::MALAGASY,
        self::MAORI,
        self::MACEDONIAN,
        self::MALAYALAM,
        self::MONGOLIAN,
        self::MOLDAVIAN,
        self::MARATHI,
        self::MALAY,
        self::MALTESE,
        self::BURMESE,
        self::NAURU,
        self::NEPALI,
        self::DUTCH,
        self::NORWEGIAN,
        self::OCCITAN,
        self::OROMOOR_ORIYA,
        self::PUNJABI,
        self::POLISH,
        self::PASHTO_PUSHTO,
        self::PORTUGUESE,
        self::QUECHUA,
        self::RHAETO_ROMANCE,
        self::KIRUNDI,
        self::ROMANIAN,
        self::RUSSIAN,
        self::KINYARWANDA,
        self::SANSKRIT,
        self::SINDHI,
        self::SANGRO,
        self::SERBO_CROATIAN,
        self::SINGHALESE,
        self::SLOVAK,
        self::SLOVENIAN,
        self::SAMOAN,
        self::SHONA,
        self::SOMALI,
        self::ALBANIAN,
        self::SERBIAN,
        self::SISWATI,
        self::SESOTHO,
        self::SUNDANESE,
        self::SWEDISH,
        self::SWAHILI,
        self::TAMIL,
        self::TEGULU,
        self::TAJIK,
        self::THAI,
        self::TIGRINYA,
        self::TURKMEN,
        self::TAGALOG,
        self::SETSWANA,
        self::TONGA,
        self::TURKISH,
        self::TSONGA,
        self::TATAR,
        self::TWI,
        self::UKRAINIAN,
        self::URDU,
        self::UZBEK,
        self::VIETNAMESE,
        self::VOLAPUK,
        self::WOLOF,
        self::XHOSA,
        self::YORUBA,
        self::CHINESE,
        self::ZULU
    ];
}