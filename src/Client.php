<?php

namespace FatihOzpolat\Param;

use SoapClient;

/**
 * ParamSoap
 *
 * @method mixed BIN_SanalPos(array $parameters = null)
 * @method mixed BIN_SanalPos2(array $parameters = null)
 * @method mixed TP_Ozel_Oran_Liste(array $parameters = null)
 * @method mixed TP_Ozel_Oran_SK_Liste(array $parameters = null)
 * @method mixed TP_Ozel_Oran_SK_Guncelle(array $parameters = null)
 * @method mixed TP_Islem_Dekont_Gonder(array $parameters = null)
 * @method mixed TP_Islem_Odeme(array $parameters = null)
 * @method mixed TP_WMD_Pay(array $parameters = null)
 * @method mixed TP_WMD_UCD_v2(array $parameters = null)
 * @method mixed TP_WMD_UCD(array $parameters = null)
 * @method mixed TP_WMD_UCD_WP(array $parameters = null)
 * @method mixed TP_WMD_UCD_Puan(array $parameters = null)
 * @method mixed Payment_Hopi(array $parameters = null)
 * @method mixed TP_Multiple_Payment(array $parameters = null)
 * @method mixed TP_Multiple_Payment_Status(array $parameters = null)
 * @method mixed TP_Islem_Odeme_WNS(array $parameters = null)
 * @method mixed TP_Islem_Odeme_WD(array $parameters = null)
 * @method mixed TP_Islem_Odeme_BKM(array $parameters = null)
 * @method mixed TP_Islem_Odeme_WKS(array $parameters = null)
 * @method mixed KK_Saklama(array $parameters = null)
 * @method mixed KK_Sakli_Liste(array $parameters = null)
 * @method mixed Pos_Odeme(array $parameters = null)
 * @method mixed TP_Islem_Sorgulama(array $parameters = null)
 * @method mixed TP_Islem_Sorgulama4(array $parameters = null)
 * @method mixed TP_Islem_Sorgulama5(array $parameters = null)
 * @method mixed TP_Islem_Sorgulama6(array $parameters = null)
 * @method mixed TP_Islem_Sorgulama_WP(array $parameters = null)
 * @method mixed TP_Mutabakat_Detay(array $parameters = null)
 * @method mixed TP_Mutabakat_Detay2(array $parameters = null)
 * @method mixed TP_Mutabakat_Ozet(array $parameters = null)
 * @method mixed TP_Islem_Iptal_Iade(array $parameters = null)
 * @method mixed TP_Islem_Iptal_Iade2(array $parameters = null)
 * @method mixed TP_Islem_Izleme(array $parameters = null)
 * @method mixed TP_Islem_Iptal_Iade_Kismi(array $parameters = null)
 * @method mixed TP_Islem_Iptal_Iade_Kismi2(array $parameters = null)
 * @method mixed TP_Islem_Iptal_Iade_Kismi3(array $parameters = null)
 * @method mixed TP_Islem_Iptal_Iade_Kismi4(array $parameters = null)
 * @method mixed TP_Islem_Iptal_Iade_Kismi_WP(array $parameters = null)
 * @method mixed TP_Islem_Iptal_OnProv(array $parameters = null)
 * @method mixed TP_KK_Verify(array $parameters = null)
 * @method mixed TP_Islem_Odeme_OnProv_Kapa(array $parameters = null)
 * @method mixed TP_Islem_Odeme_OnProv(array $parameters = null)
 * @method mixed TP_Islem_Odeme_OnProv_WMD(array $parameters = null)
 * @method mixed TP_Islem_Odeme_OnProv_WKS(array $parameters = null)
 * @method mixed TP_Islem_Checkout_SK(array $parameters = null)
 * @method mixed TP_Islem_Checkout(array $parameters = null)
 * @method mixed TP_Islem_Checkout_Odeme_WNS(array $parameters = null)
 * @method mixed SHA2B64(array $parameters = null)
 * @method mixed Vadeli_Islem_Izleme(array $parameters = null)
 * @method mixed Pazaryeri_TP_AltUyeIsyeri_Ekleme(array $parameters = null)
 * @method mixed Pazaryeri_TP_AltUyeIsyeri_Ekleme_v2(array $parameters = null)
 * @method mixed Pazaryeri_TP_AltUyeIsyeri_Guncelleme(array $parameters = null)
 * @method mixed Pazaryeri_TP_AltUyeIsyeri_Guncelleme_v2(array $parameters = null)
 * @method mixed Pazaryeri_TP_AltUyeIsyeri_Liste(array $parameters = null)
 * @method mixed Pazaryeri_TP_AltUyeIsyeri_Silme(array $parameters = null)
 * @method mixed Pazaryeri_TP_Iptal_Iade(array $parameters = null)
 * @method mixed Pazaryeri_TP_Limit_Kontrol(array $parameters = null)
 * @method mixed Pazaryeri_TP_Siparis_Detay_Ekle(array $parameters = null)
 * @method mixed MP_OrderDetailAdd(array $parameters = null)
 * @method mixed MP_OrderDetailUpdate(array $parameters = null)
 * @method mixed MP_OrderDetailStatus(array $parameters = null)
 * @method mixed MP_OrderDetailQuery(array $parameters = null)
 * @method mixed MP_OrderRefund(array $parameters = null)
 * @method mixed MP_OrderDetailList(array $parameters = null)
 * @method mixed TP_Modal_Payment(array $parameters = null)
 * @method mixed Param_Pazaryeri_Islem_Izleme(array $parameters = null)
 * @method mixed Pazaryeri_TP_Siparis_Onay(array $parameters = null)
 * @method mixed Pazaryeri_TP_Islem_Sorgulama(array $parameters = null)
 * @method mixed Pos_Plugin_Bildirim(array $parameters = null)
 * @method mixed Il_Liste(array $parameters = null)
 * @method mixed MoneyPay_Islem_Sorgulama(array $parameters = null)
 * @method mixed KS_Kart_Ekle(array $parameters = null)
 * @method mixed KS_Tahsilat(array $parameters = null)
 * @method mixed KS_Kart_Liste(array $parameters = null)
 * @method mixed KS_Kart_Sil (array $parameters = null)
 */
class Client extends SoapClient
{
    const TEST_URL = 'https://test-dmz.param.com.tr/turkpos.ws/service_turkpos_test.asmx?wsdl';

    const LIVE_URL = 'https://posws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx?wsdl';

    public const PROD_SAVE_CARD_URL = 'https://posws.param.com.tr/out.ws/service_ks.asmx?wsdl';

    public const TEST_SAVE_CARD_URL = 'https://test-dmz.param.com.tr/out.ws/service_ks.asmx?wsdl';

    public function __construct($type = 'payment')
    {
        $wsdl = config('param-pos.test') ? self::TEST_URL : self::LIVE_URL;

        if ($type === 'card') {
            $wsdl = config('param-pos.test') ? self::TEST_SAVE_CARD_URL : self::PROD_SAVE_CARD_URL;
        }

        parent::__construct($wsdl, [
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ]),
        ]);
    }
}
