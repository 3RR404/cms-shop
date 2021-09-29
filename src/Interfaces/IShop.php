<?php

namespace Weblike\Cms\Shop\Interfaces;

interface IShop
{
    const BANK_NAME = [
        '0200' => 'Všeobecná úverová banka, a. s.',
        '0720' => 'Národná banka Slovenska',
        '0900' => 'Slovenská sporiteľňa, a. s.',
        '1100' => 'Tatra banka, a. s.',
        '1111' => 'UniCredit Bank Czech Republic and Slovakia, a.s., pobočka zahraničnej banky',
        '3000' => 'Slovenská záručná a rozvojová banka, a. s.',
        '3100' => 'Prima banka Slovensko, a.s. (predtým Sberbank Slovensko, a.s.) – kód pre dobeh platieb',
        '5200' => 'OTP Banka Slovensko, a. s.',
        '5600' => 'Prima banka Slovensko, a.s',
        '5900' => 'Prvá stavebná sporiteľňa, a. s.',
        '6500' => 'Poštová banka, a. s.',
        '7500' => 'Československá obchodná banka, a. s.',
        '7930' => 'Wüstenrot stavebná sporiteľňa, a. s.',
        '8050' => 'COMMERZBANK Aktiengesellschaft, pobočka zahraničnej banky, Bratislava',
        '8100' => 'Komerční banka a.s. pobočka zahraničnej banky',
        '8120' => 'Privatbanka, a. s.',
        '8130' => 'Citibank Europe plc,&nbsp;&nbsp;pobočka zahraničnej banky',
        '8160' => 'EXIMBANKA SR',
        '8320' => 'J &amp; T BANKA, a. s., pobočka zahraničnej banky',
        '8330' => 'Fio banka, a.s., pobočka zahraničnej banky',
        '8360' => 'mBank S.A., pobočka zahraničnej banky',
    ];

    const BANK_SWIFT = [
        0200 => 'SUBASKBX',
        0720 => 'NBSBSKBX',
        '0900' => 'GIBASKBX',
        1100 => 'TATRSKBX',
        1111 => 'UNCRSKBX',
        3000 => 'SLZBSKBA',
        3100 => 'LUBASKBX',
        5200 => 'OTPVSKBX',
        5600 => 'KOMASK2X',
        5900 => 'PRVASKBA',
        6500 => 'POBNSKBA',
        7500 => 'CEKOSKBX',
        7930 => 'WUSTSKBA',
        8050 => 'COBASKBX',
        8100 => 'KOMBSKBA',
        8120 => 'BSLOSK22',
        8130 => 'CITISKBA',
        8170 => 'KBSPSKBX',
    	8160 => 'EXSKSKBX',
        8180 => 'SPSRSKBA',
        8320 => 'JTBPSKBA',
        8330 => 'FIOZSKBAXXX',
        8360 => 'BREXSKBX',
    ];

    const BANK_CODE_VUB = 0200;
    const BANK_CODE_NBS = 0720;
    const BANK_CODE_SLSP = '0900';
    const BANK_CODE_TB = 1100;
    const BANK_CODE_SZRB = 1111;
    const BANK_CODE_OTPB = 3000;
    const BANK_CODE_PRIMA = 3100;
    const BANK_CODE_PSS = 5900;
    const BANK_CODE_PB = 6500;
    const BANK_CODE_CSOB = 7500;
    const BANK_CODE_WSS = 7930;
    const BANK_CODE_KOA = 8050;
    const BANK_CODE_KO = 8100;
    const BANK_CODE_PRIVAT = 8120;
    const BANK_CODE_CITY = 8130;
    const BANK_CODE_EXIM = 8160;
    const BANK_CODE_CSOBSS = 8170;
    const BANK_CODE_SP = 8180;
    const BANK_CODE_JANDT = 8320;
    const BANK_CODE_FIO = 8330;
    const BANK_CODE_MBANK = 8360;
}