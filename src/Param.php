<?php

namespace FatihOzpolat\Param;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Param
{
    protected Client $client;

    protected Client $ks_client;

    protected array $G;

    protected string $GUID;

    /**
     * Param constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        if (! extension_loaded('soap')) {
            throw new Exception('PARAM: SOAP extension not loaded.');
        }

        $this->client = new Client();
        $this->ks_client = new Client('card');

        $this->G = [
            'CLIENT_CODE' => config('param-pos.client_code'),
            'CLIENT_USERNAME' => config('param-pos.client_username'),
            'CLIENT_PASSWORD' => config('param-pos.client_password'),
        ];

        $this->GUID = config('param-pos.guid');
    }

    /**
     * Kredi kartı saklama için kullanılır
     *
     * @link https://dev.param.com.tr/tr/api/kart-saklama #Kart Saklama
     *
     * @param  string  $kk_sahibi Kredi Kartı Sahibinin Adı Soyadı
     * @param  string  $kk_no Kredi Kartı Numarası
     * @param  string  $kk_sk_ay Kredi Kartının Son Kullanma Tarihi (Ay)
     * @param  string  $kk_sk_yil Kredi Kartının Son Kullanma Tarihi (Yıl)
     * @param  string|null  $kk_kart_adi Saklanacak Kredi Kartı adı, Opsiyonel
     * @param  string|null  $kk_islem_id Saklanacak Kredi Kartına ait tarafınıza iletilecek tekil ID değeri
     *
     * @throws Exception
     */
    public function ks_kart_ekle(
        string $kk_sahibi,
        string $kk_no,
        string $kk_sk_ay,
        string $kk_sk_yil,
        string $kk_kart_adi = null,
        string $kk_islem_id = null
    ): array {
        $res = $this->ks_client->KS_Kart_Ekle([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'KK_Sahibi' => $kk_sahibi,
            'KK_No' => $kk_no,
            'KK_SK_Ay' => $kk_sk_ay,
            'KK_SK_Yil' => $kk_sk_yil,
            'KK_Kart_Adi' => $kk_kart_adi,
            'KK_Islem_ID' => $kk_islem_id,
        ]);

        $res = $res->KS_Kart_EkleResult;

        if ($res->Sonuc < 0) {
            throw new Exception($res->Sonuc_Str);
        }

        return $this->res($res);
    }

    /**
     * Saklanmış kredi kartından tahsilat yapmak için kullanılır.
     *
     * @link https://dev.param.com.tr/tr/api/kart-saklama?tab=kart-saklamali-odeme #Kart Saklama => Kart Saklamalı Ödeme
     *
     * @param  string  $ks_guid KS_Kart_Ekle metodundan dönen GUID değeri
     * @param  string  $kk_sahibi_gsm Kredi Kartı Sahibi GSM No, Başında 0 olmadan (5xxxxxxxxx)
     * @param  string  $hata_url Ödeme işlemi başarısız olursa yönlenecek sayfa adresi
     * @param  string  $basarili_url Ödeme işlemi başarılı olursa yönlenecek sayfa adresi
     * @param  string  $siparis_id Siparişe özel tekil ID. Bu değeri daha önce gönderdiyseniz sistem yeni Siparis_ID atar. İşlem sonucunda bu Siparis_ID yi döner.
     * @param  int  $taksit Seçilen Taksit Sayısı. Tek çekim için 1 gönderiniz.
     * @param  float  $islem_tutar İşlem Tutarı
     * @param  float  $toplam_tutar Komisyon Dahil Sipariş Tutarı
     * @param  string  $islem_guvenlik_tip NS (NonSecure) veya 3D gönderilir.
     * @param  string  $ip_address IP Adresi
     * @param  string|null  $cvc 3D işlemler için girilmelidir. Nonsecure işlemler için boş geçilebilir.
     * @param  string|null  $siparis_aciklama Siparişe ait açıklama
     * @param  string|null  $islem_id İşleme ait Sipariş ID haricinde tekil ID, opsiyoneldir
     * @param  string|null  $ref_url Ödemenin gerçekleştiği sayfanın URLsi
     * @param  string|null  $data1 Extra Alan 1
     * @param  string|null  $data2 Extra Alan 2
     * @param  string|null  $data3 Extra Alan 3
     * @param  string|null  $data4 Extra Alan 4
     * @param  string|null  $kk_islem_id Saklanacak Kredi Kartına ait tarafınıza iletilecek tekil ID değeri
     * @return array
     *
     * @throws Exception
     */
    public function ks_tahsilat(
        string $ks_guid,
        string $kk_sahibi_gsm,
        string $hata_url,
        string $basarili_url,
        string $siparis_id,
        int $taksit,
        float $islem_tutar,
        float $toplam_tutar,
        string $islem_guvenlik_tip,
        string $ip_address,
        string $cvc = null,
        string $siparis_aciklama = null,
        string $islem_id = null,
        string $ref_url = null,
        string $data1 = null,
        string $data2 = null,
        string $data3 = null,
        string $data4 = null,
        string $kk_islem_id = null,
    ) {
        $islem_tutar = number_format($islem_tutar, 2, ',', '');
        $toplam_tutar = number_format($toplam_tutar, 2, ',', '');
        $islem_guvenlik_tip = Str::upper($islem_guvenlik_tip);

        if (! in_array($islem_guvenlik_tip, ['NS', '3D'])) {
            throw new Exception('PARAM: KS_Tahsilat: islem_guvenlik_tip parametresi NS veya 3D olmalıdır');
        }

        $res = $this->ks_client->KS_Tahsilat([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'KS_GUID' => $ks_guid,
            'CVC' => $cvc,
            'KK_Sahibi_GSM' => $kk_sahibi_gsm,
            'Hata_URL' => $hata_url,
            'Basarili_URL' => $basarili_url,
            'Siparis_ID' => $siparis_id,
            'Siparis_Aciklama' => $siparis_aciklama,
            'Taksit' => $taksit,
            'Islem_Tutar' => $islem_tutar,
            'Toplam_Tutar' => $toplam_tutar,
            'Islem_Guvenlik_Tip' => $islem_guvenlik_tip,
            'Islem_ID' => $islem_id,
            'IPAdr' => $ip_address,
            'Ref_URL' => $ref_url,
            'Data1' => $data1,
            'Data2' => $data2,
            'Data3' => $data3,
            'Data4' => $data4,
            'KK_Islem_ID' => $kk_islem_id,
        ]);

        $res = $res->KS_TahsilatResult;

        if ($res->Sonuc < 0) {
            throw new Exception($res->Sonuc_Str);
        }

        if ($islem_guvenlik_tip == 'NS' && $res->UCD_URL !== 'NONSECURE') {
            throw new Exception('PARAM: KS_Tahsilat: UCD_URL değeri NONSECURE olmalıdır');
        }

        return $this->res($res);
    }

    /**
     * Bu metot, saklı kartların listelenmesi için kullanılır.
     * <div style="color: yellow">
     *     Yazılımcı notu: İki parametreyi null geçtim fakat en az birini gönderin yoksa çok fazla ram tüketiminden hata alabilirsiniz.
     * </div>
     *
     * @link https://dev.param.com.tr/tr/api/kart-saklama?tab=kart-saklama-listesi #Kart Saklama => Kart Saklama Listesi
     *
     * @param  string|null  $kk_kart_adi Saklanan kart adı
     * @param  string|null  $kk_islem_id Saklanan karta ait tekil ID (Opsiyoneldir)
     *
     * @throws Exception
     */
    public function ks_kart_liste(
        string $kk_kart_adi = null,
        string $kk_islem_id = null
    ): array {
        $res = $this->ks_client->KS_Kart_Liste([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'KK_Kart_Adi' => $kk_kart_adi,
            'KK_Islem_ID' => $kk_islem_id,
        ]);

        $res = $res->KS_Kart_ListeResult;

        if ($res->Sonuc < 0) {
            throw new Exception($res->Sonuc_Str);
        }

        $diffgram = $this->diffgram($res);

        return $diffgram['NewDataSet']['Temp'];
    }

    /**
     * Bu metot, saklı kart silmek için kullanılır.
     *
     * @link https://dev.param.com.tr/tr/api/kart-saklama?tab=sakli-kart-silme #Kart Saklama => Saklı Kart Silme
     *
     * @param  string  $ks_guid Saklı kartın GUID Değeri
     * @param  string  $kk_islem_id Saklanacak Kredi Kartına ait tarafınızdan iletilecek tekil ID değeri
     *
     * @throws Exception
     */
    public function ks_kart_sil(
        string $ks_guid,
        string $kk_islem_id
    ): array {
        $res = $this->ks_client->KS_Kart_Sil([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'KS_GUID' => $ks_guid,
            'KK_Islem_ID' => $kk_islem_id,
        ]);

        $res = $res->KS_Kart_SilResult;

        if ($res->Sonuc < 0) {
            throw new Exception($res->Sonuc_Str);
        }

        return $this->res($res);
    }

    /**
     * Kart doğrulama sağlamak için kullanılır.
     *
     * @link https://dev.param.com.tr/tr/api/kart-saklama?tab=kart-dogrulama #Kart Saklama => Kart Doğrulama
     *
     * @param  string  $kk_no Kredi Kartı numarası
     * @param  string  $kk_sk_ay 2 haneli Son Kullanma Ay
     * @param  string  $kk_sk_yil 4 haneli Son Kullanma Yıl
     * @param  string  $kk_cvc CVC Kodu
     * @param  string|null  $return_url Sonuç post parametrelerinin döndüğü URL’dir.
     * @param  string|null  $data1 Extra Alan 1
     * @param  string|null  $data2 Extra Alan 2
     * @param  string|null  $data3 Extra Alan 3
     * @param  string|null  $data4 Extra Alan 4
     * @param  string|null  $data5 Extra Alan 5
     *
     * @throws Exception
     */
    public function tp_kk_verify(
        string $kk_no,
        string $kk_sk_ay,
        string $kk_sk_yil,
        string $kk_cvc,
        string $return_url = null,
        string $data1 = null,
        string $data2 = null,
        string $data3 = null,
        string $data4 = null,
        string $data5 = null
    ): array {
        $res = $this->client->TP_KK_Verify([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'KK_No' => $kk_no,
            'KK_SK_Ay' => $kk_sk_ay,
            'KK_SK_Yil' => $kk_sk_yil,
            'KK_CVC' => $kk_cvc,
            'Return_URL' => $return_url,
            'Data1' => $data1,
            'Data2' => $data2,
            'Data3' => $data3,
            'Data4' => $data4,
            'Data5' => $data5,
        ]);

        $res = $res->TP_KK_VerifyResult;

        if ($res->Sonuc < 0) {
            throw new Exception($res->Sonuc_Str);
        }

        return $this->res($res);
    }

    /**
     * Nonsecure/3D ödeme işleminin başlatılacağı metottur. 3D işlemler için metot sonucu dönen 3D Banka HTML kod içeriği ekrana bastırılır ve yönlendirme yapılmış olur. Kredi kartı doğrulama işlemi başlar.
     *
     * Kontrol Senaryoları
     *
     * Entegrasyon sırasında yapılmasını beklediğimiz test senaryolarına {@link https://dev.param.com.tr/dosya/test-scenarios.pdf #buradan} ulaşabilirsiniz.
     *
     * <div style="color: yellow">
     * Yazılımcı notu: Gözlemlediğim kadarıyla işlem tutar bir işe yaramıyor ne gönderirsem göndereyim sistem toplam tutarı baz alıyor.
     * Bu nedenle tek tek komisyon hesaplamaya çalışmayın ikisinide toplam tutar şeklinde gönderin.
     * </div>
     *
     * @link https://dev.param.com.tr/tr/api/odeme #Ödeme => Satış 3D Model/NS
     *
     * @param  string  $kk_sahibi Kredi Kartı Sahibi
     * @param  string  $kk_no Kredi Kartı numarası
     * @param  string  $kk_sk_ay 2 hane Son Kullanma Ay
     * @param  string  $kk_sk_yil 4 haneli Son Kullanma Yıl
     * @param  string  $kk_cvc CVC Kodu
     * @param  string  $hata_url Ödeme işlemi başarısız olursa yönlenecek sayfa adresi
     * @param  string  $basarili_url Ödeme işlemi başarılı olursa yönlenecek sayfa adresi
     * @param  string  $siparis_id Siparişe özel tekil ID. Bu değeri daha önce gönderdiyseniz sistem yeni Siparis_ID atar. İşlem sonucunda bu Siparis_ID yi döner.
     * @param  int  $taksit Seçilen Taksit Sayısı. Tek çekim için 1 gönderiniz.
     * @param  float  $islem_tutar Sipariş Tutarı
     * @param  float  $toplam_tutar Komisyon Dahil Sipariş Tutarı
     * @param  string  $islem_guvenlik_tip NS (NonSecure) veya 3D
     * @param  string  $ip_address IP Adresi
     * @param  string|null  $kk_sahibi_gsm Kredi Kartı Sahibi GSM No, Başında 0 olmadan (5xxxxxxxxx)
     * @param  string|null  $siparis_aciklama Siparişe ait açıklama
     * @param  string|null  $islem_id İşleme ait Sipariş ID haricinde tekil ID opsiyoneldir.
     * @param  string|null  $ref_url Ödemenin gerçekleştiği sayfanın URLsi
     * @param  string|null  $Data1 Extra Alan 1
     * @param  string|null  $Data2 Extra Alan 2
     * @param  string|null  $Data3 Extra Alan 3
     * @param  string|null  $Data4 Extra Alan 4
     * @param  string|null  $Data5 Extra Alan 5
     *
     * @throws Exception
     */
    public function tp_wmd_ucd(
        string $kk_sahibi,
        string $kk_no,
        string $kk_sk_ay,
        string $kk_sk_yil,
        string $kk_cvc,
        string $hata_url,
        string $basarili_url,
        string $siparis_id,
        int $taksit,
        float $islem_tutar,
        float $toplam_tutar,
        string $islem_guvenlik_tip,
        string $ip_address,
        string $kk_sahibi_gsm = null,
        string $siparis_aciklama = null,
        string $islem_id = null,
        string $ref_url = null,
        string $Data1 = null,
        string $Data2 = null,
        string $Data3 = null,
        string $Data4 = null,
        string $Data5 = null
    ): array {
        $islem_guvenlik_tip = Str::upper($islem_guvenlik_tip);
        if (! in_array($islem_guvenlik_tip, ['NS', '3D'])) {
            throw new Exception('PARAM: TP_WMD_UCD: islem_guvenlik_tip parametresi NS veya 3D olmalıdır');
        }

        $islem_tutar = number_format($islem_tutar, 2, ',', '');
        $toplam_tutar = number_format($toplam_tutar, 2, ',', '');

        $islem_hash = $this->sha2b64(config('param-pos.client_code').config('param-pos.guid').$taksit.$islem_tutar.$toplam_tutar.$siparis_id);

        $res = $this->client->TP_WMD_UCD([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'KK_Sahibi' => $kk_sahibi,
            'KK_No' => $kk_no,
            'KK_SK_Ay' => $kk_sk_ay,
            'KK_SK_Yil' => $kk_sk_yil,
            'KK_CVC' => $kk_cvc,
            'KK_Sahibi_GSM' => $kk_sahibi_gsm,
            'Hata_URL' => $hata_url,
            'Basarili_URL' => $basarili_url,
            'Siparis_ID' => $siparis_id,
            'Siparis_Aciklama' => $siparis_aciklama,
            'Taksit' => $taksit,
            'Islem_Tutar' => $islem_tutar,
            'Toplam_Tutar' => $toplam_tutar,
            'Islem_Hash' => $islem_hash,
            'Islem_Guvenlik_Tip' => $islem_guvenlik_tip,
            'Islem_ID' => $islem_id,
            'IPAdr' => $ip_address,
            'Ref_URL' => $ref_url,
            'Data1' => $Data1,
            'Data2' => $Data2,
            'Data3' => $Data3,
            'Data4' => $Data4,
            'Data5' => $Data5,
        ]);

        $res = $res->TP_WMD_UCDResult;

        if ($res->Sonuc < 0) {
            throw new Exception($res->Sonuc_Str);
        }

        if ($islem_guvenlik_tip == 'NS' && $res->UCD_HTML !== 'NONSECURE') {
            throw new Exception('PARAM: TP_WMD_UCD: UCD_HTML değeri NONSECURE olmalıdır');
        }

        return $this->res($res);
    }

    /**
     * Bu metot, Nonsecure / 3D ödeme işleminin başlatılacağı metottur. (SanalPOS_ID parametresi kaldırılmıştır.) İşlem sonucu dönen 3D URL sine yönlendirme yapılır ve kredi kartı ile ödeme işlemi başlar.
     *
     * Kontrol Senaryoları
     *
     * Entegrasyon sırasında yapılmasını beklediğimiz test senaryolarına {@link https://dev.param.com.tr/dosya/test-scenarios.pdf #buradan} ulaşabilirsiniz.
     *
     * <div style="color: yellow">
     * Yazılımcı notu: Gözlemlediğim kadarıyla işlem tutar bir işe yaramıyor ne gönderirsem göndereyim sistem toplam tutarı baz alıyor.
     * Bu nedenle tek tek komisyon hesaplamaya çalışmayın ikisinide toplam tutar şeklinde gönderin.
     * </div>
     *
     * @link https://dev.param.com.tr/tr/api/odeme?tab=oedeme-v2 #Ödeme => Satış 3D Pay/NS
     *
     * @param  string  $kk_sahibi Kredi Kartı Sahibi
     * @param  string  $kk_no Kredi Kartı numarası
     * @param  string  $kk_sk_ay 2 hane Son Kullanma Ay
     * @param  string  $kk_sk_yil 4 haneli Son Kullanma Yıl
     * @param  string  $kk_cvc CVC Kodu
     * @param  string  $hata_url Ödeme işlemi başarısız olursa yönlenecek sayfa adresi
     * @param  string  $basarili_url Ödeme işlemi başarılı olursa yönlenecek sayfa adresi
     * @param  string  $siparis_id Siparişe özel tekil ID. Bu değeri daha önce gönderdiyseniz sistem yeni Siparis_ID atar. İşlem sonucunda bu Siparis_ID yi döner.
     * @param  int  $taksit Seçilen Taksit Sayısı. Tek çekim için 1 gönderiniz.
     * @param  float  $islem_tutar Sipariş Tutarı
     * @param  float  $toplam_tutar Komisyon Dahil Sipariş Tutarı
     * @param  string  $islem_guvenlik_tip NS (NonSecure) veya 3D
     * @param  string  $ip_address IP Adresi
     * @param  string|null  $kk_sahibi_gsm Kredi Kartı Sahibi GSM No, Başında 0 olmadan (5xxxxxxxxx)
     * @param  string|null  $siparis_aciklama Siparişe ait açıklama
     * @param  string|null  $islem_id İşleme ait Sipariş ID haricinde tekil ID opsiyoneldir.
     * @param  string|null  $ref_url Ödemenin gerçekleştiği sayfanın URLsi
     * @param  string|null  $Data1 Extra Alan 1
     * @param  string|null  $Data2 Extra Alan 2
     * @param  string|null  $Data3 Extra Alan 3
     * @param  string|null  $Data4 Extra Alan 4
     * @param  string|null  $Data5 Extra Alan 5
     *
     * @throws Exception
     */
    public function pos_odeme(
        string $kk_sahibi,
        string $kk_no,
        string $kk_sk_ay,
        string $kk_sk_yil,
        string $kk_cvc,
        string $hata_url,
        string $basarili_url,
        string $siparis_id,
        int $taksit,
        float $islem_tutar,
        float $toplam_tutar,
        string $islem_guvenlik_tip,
        string $ip_address,
        string $kk_sahibi_gsm = null,
        string $siparis_aciklama = null,
        string $islem_id = null,
        string $ref_url = null,
        string $Data1 = null,
        string $Data2 = null,
        string $Data3 = null,
        string $Data4 = null,
        string $Data5 = null
    ): array {
        $islem_guvenlik_tip = Str::upper($islem_guvenlik_tip);
        if (! in_array($islem_guvenlik_tip, ['NS', '3D'])) {
            throw new Exception('PARAM: Pos_Odeme: islem_guvenlik_tip parametresi NS veya 3D olmalıdır');
        }

        $islem_tutar = number_format($islem_tutar, 2, ',', '');
        $toplam_tutar = number_format($toplam_tutar, 2, ',', '');

        $islem_hash = $this->sha2b64(config('param-pos.client_code').config('param-pos.guid').$taksit.$islem_tutar.$toplam_tutar.$siparis_id.$hata_url.$basarili_url);

        $res = $this->client->Pos_Odeme([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'KK_Sahibi' => $kk_sahibi,
            'KK_No' => $kk_no,
            'KK_SK_Ay' => $kk_sk_ay,
            'KK_SK_Yil' => $kk_sk_yil,
            'KK_CVC' => $kk_cvc,
            'KK_Sahibi_GSM' => $kk_sahibi_gsm,
            'Hata_URL' => $hata_url,
            'Basarili_URL' => $basarili_url,
            'Siparis_ID' => $siparis_id,
            'Siparis_Aciklama' => $siparis_aciklama,
            'Taksit' => $taksit,
            'Islem_Tutar' => $islem_tutar,
            'Toplam_Tutar' => $toplam_tutar,
            'Islem_Hash' => $islem_hash,
            'Islem_Guvenlik_Tip' => $islem_guvenlik_tip,
            'Islem_ID' => $islem_id,
            'IPAdr' => $ip_address,
            'Ref_URL' => $ref_url,
            'Data1' => $Data1,
            'Data2' => $Data2,
            'Data3' => $Data3,
            'Data4' => $Data4,
            'Data5' => $Data5,
        ]);

        $res = $res->Pos_OdemeResult;

        if ($res->Sonuc < 0) {
            throw new Exception($res->Sonuc_Str);
        }

        if ($islem_guvenlik_tip == 'NS' && $res->UCD_URL !== 'NONSECURE') {
            throw new Exception('PARAM: Pos_Odeme: UCD_URL değeri NONSECURE olmalıdır');
        }

        return $this->res($res);
    }

    /**
     * Nonsecure/3D ödeme işleminin başlatılacağı metottur. 3D işlemler için metot sonucu dönen 3D Banka HTML kod içeriği ekrana bastırılır ve yönlendirme yapılmış olur. Kredi kartı doğrulama işlemi başlar.
     *
     * Kontrol Senaryoları
     *
     * Entegrasyon sırasında yapılmasını beklediğimiz test senaryolarına {@link https://dev.param.com.tr/dosya/test-scenarios.pdf #buradan} ulaşabilirsiniz.
     *
     * <div style="color: yellow">
     * Yazılımcı notu: Gözlemlediğim kadarıyla işlem tutar bir işe yaramıyor ne gönderirsem göndereyim sistem toplam tutarı baz alıyor.
     * Bu nedenle tek tek komisyon hesaplamaya çalışmayın ikisinide toplam tutar şeklinde gönderin.
     * </div>
     *
     * @link https://dev.param.com.tr/tr/api/on-provizyon?tab=provizyon-kapatma-islemi #Ön Provizyon => 3D Model/NS
     *
     * @param  string  $kk_sahibi Kredi Kartı Sahibi
     * @param  string  $kk_no Kredi Kartı numarası
     * @param  string  $kk_sk_ay 2 hane Son Kullanma Ay
     * @param  string  $kk_sk_yil 4 haneli Son Kullanma Yıl
     * @param  string  $kk_cvc CVC Kodu
     * @param  string  $hata_url Ödeme işlemi başarısız olursa yönlenecek sayfa adresi
     * @param  string  $basarili_url Ödeme işlemi başarılı olursa yönlenecek sayfa adresi
     * @param  string  $siparis_id Siparişe özel tekil ID. Bu değeri daha önce gönderdiyseniz sistem yeni Siparis_ID atar. İşlem sonucunda bu Siparis_ID yi döner.
     * @param  int  $taksit Seçilen Taksit Sayısı. Tek çekim için 1 gönderiniz.
     * @param  float  $islem_tutar Sipariş Tutarı
     * @param  float  $toplam_tutar Komisyon Dahil Sipariş Tutarı
     * @param  string  $islem_guvenlik_tip NS (NonSecure) veya 3D
     * @param  string  $ip_address IP Adresi
     * @param  string|null  $kk_sahibi_gsm Kredi Kartı Sahibi GSM No, Başında 0 olmadan (5xxxxxxxxx)
     * @param  string|null  $siparis_aciklama Siparişe ait açıklama
     * @param  string|null  $islem_id İşleme ait Sipariş ID haricinde tekil ID opsiyoneldir.
     * @param  string|null  $ref_url Ödemenin gerçekleştiği sayfanın URLsi
     * @param  string|null  $Data1 Extra Alan 1
     * @param  string|null  $Data2 Extra Alan 2
     * @param  string|null  $Data3 Extra Alan 3
     * @param  string|null  $Data4 Extra Alan 4
     * @param  string|null  $Data5 Extra Alan 5
     *
     * @throws Exception
     */
    public function tp_islem_odeme_onprov_wmd(
        string $kk_sahibi,
        string $kk_no,
        string $kk_sk_ay,
        string $kk_sk_yil,
        string $kk_cvc,
        string $hata_url,
        string $basarili_url,
        string $siparis_id,
        int $taksit,
        float $islem_tutar,
        float $toplam_tutar,
        string $islem_guvenlik_tip,
        string $ip_address,
        string $kk_sahibi_gsm = null,
        string $siparis_aciklama = null,
        string $islem_id = null,
        string $ref_url = null,
        string $Data1 = null,
        string $Data2 = null,
        string $Data3 = null,
        string $Data4 = null,
        string $Data5 = null
    ): array {
        $islem_guvenlik_tip = Str::upper($islem_guvenlik_tip);
        if (! in_array($islem_guvenlik_tip, ['NS', '3D'])) {
            throw new Exception('PARAM: TP_Islem_Odeme_OnProv_WMD: islem_guvenlik_tip parametresi NS veya 3D olmalıdır');
        }

        $islem_tutar = number_format($islem_tutar, 2, ',', '');
        $toplam_tutar = number_format($toplam_tutar, 2, ',', '');

        $islem_hash = $this->sha2b64(config('param-pos.client_code').config('param-pos.guid').$islem_tutar.$toplam_tutar.$siparis_id.$hata_url.$basarili_url);

        $res = $this->client->TP_Islem_Odeme_OnProv_WMD([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'KK_Sahibi' => $kk_sahibi,
            'KK_No' => $kk_no,
            'KK_SK_Ay' => $kk_sk_ay,
            'KK_SK_Yil' => $kk_sk_yil,
            'KK_CVC' => $kk_cvc,
            'KK_Sahibi_GSM' => $kk_sahibi_gsm,
            'Hata_URL' => $hata_url,
            'Basarili_URL' => $basarili_url,
            'Siparis_ID' => $siparis_id,
            'Siparis_Aciklama' => $siparis_aciklama,
            'Taksit' => $taksit,
            'Islem_Tutar' => $islem_tutar,
            'Toplam_Tutar' => $toplam_tutar,
            'Islem_Hash' => $islem_hash,
            'Islem_Guvenlik_Tip' => $islem_guvenlik_tip,
            'Islem_ID' => $islem_id,
            'IPAdr' => $ip_address,
            'Ref_URL' => $ref_url,
            'Data1' => $Data1,
            'Data2' => $Data2,
            'Data3' => $Data3,
            'Data4' => $Data4,
            'Data5' => $Data5,
        ]);

        $res = $res->TP_Islem_Odeme_OnProv_WMDResult;

        if ($res->Sonuc < 0) {
            throw new Exception($res->Sonuc_Str);
        }

        if ($islem_guvenlik_tip == 'NS' && $res->UCD_HTML !== 'NONSECURE') {
            throw new Exception('PARAM: TP_Islem_Odeme_OnProv_WMD: UCD_HTML değeri NONSECURE olmalıdır');
        }

        return $this->res($res);
    }

    /**
     * Ön provizyon işlemini satışa dönüştürür.
     *
     * @link https://dev.param.com.tr/tr/api/on-provizyon?tab=provizyon-kapatma-islemi #Ön Provizyon => Kapama İşlemi
     *
     * @param  string  $siparis_id Sipariş ID Değeri
     * @param  float  $prov_tutar Kapama Yapılacak Tutar
     * @param  string|null  $prov_id Provizyon ID (Opsiyonel, boş geçilebilir)
     *
     * @throws Exception
     */
    public function tp_islem_odeme_onprov_kapa(
        string $siparis_id,
        float $prov_tutar,
        string $prov_id = null
    ): array {
        $prov_tutar = number_format($prov_tutar, 2, ',', '');

        $res = $this->client->TP_Islem_Odeme_OnProv_Kapa([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'Siparis_ID' => $siparis_id,
            'Prov_Tutar' => $prov_tutar,
            'Prov_ID' => $prov_id,
        ]);

        $res = $res->TP_Islem_Odeme_OnProv_KapaResult;

        if ($res->Sonuc < 0) {
            throw new Exception($res->Sonuc_Str);
        }

        return $this->res($res);
    }

    /**
     * Bu metot, satış işlemi yapılmamış Provizyon iptali için kullanılır.
     *
     * @link https://dev.param.com.tr/tr/api/on-provizyon?tab=on-provizyon-iptali #Ön Provizyon => İptal İşlemi
     *
     * @param  string  $siparis_id Sipariş ID Değeri
     * @param  string|null  $prov_id Provizyon ID (Opsiyonel, boş geçilebilir)
     *
     * @throws Exception
     */
    public function tp_islem_iptal_onprov(
        string $siparis_id,
        string $prov_id = null
    ): array {
        $res = $this->client->TP_Islem_Iptal_OnProv([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'Siparis_ID' => $siparis_id,
            'Prov_ID' => $prov_id,
        ]);

        $res = $res->TP_Islem_Iptal_OnProvResult;

        if ($res->Sonuc < 0) {
            throw new Exception($res->Sonuc_Str);
        }

        return $this->res($res);
    }

    /**
     * Bu metot, dövizle işlem yapmak için kullanılır.
     *
     * Bu method sadece yabancı kartlar ile çalışmaktadır.
     *
     * @link https://dev.param.com.tr/tr/api/doviz-ile-odeme #Döviz İle Ödeme
     *
     * @param  int  $doviz_kodu Döviz Kodu (1000 TurkPara - TRL, 1001 TurkPara - USD, 1002 TurkPara - EUR, 1003 Turkpara - GBP)
     * @param  string  $kk_sahibi Kredi Kartı Sahibi
     * @param  string  $kk_no Kredi Kartı numarası
     * @param  string  $kk_sk_ay 2 hane Son Kullanma Ay
     * @param  string  $kk_sk_yil 4 haneli Son Kullanma Yıl
     * @param  string  $kk_cvc CVC Kodu
     * @param  string  $hata_url Ödeme işlemi başarısız olursa yönlenecek sayfa adresi
     * @param  string  $basarili_url Ödeme işlemi başarılı olursa yönlenecek sayfa adresi
     * @param  string  $siparis_id Siparişe özel tekil ID. Bu değeri daha önce gönderdiyseniz sistem yeni Siparis_ID atar. İşlem sonucunda bu Siparis_ID yi döner.
     * @param  float  $islem_tutar Sipariş Tutarı
     * @param  float  $toplam_tutar Komisyon Dahil Sipariş Tutarı
     * @param  string  $islem_guvenlik_tip NS (NonSecure) veya 3D
     * @param  string  $ip_address IP Adresi
     * @param  string|null  $kk_sahibi_gsm Kredi Kartı Sahibi GSM No, Başında 0 olmadan (5xxxxxxxxx)
     * @param  string|null  $siparis_aciklama Siparişe ait açıklama
     * @param  string|null  $islem_id İşleme ait Sipariş ID haricinde tekil ID opsiyoneldir.
     * @param  string|null  $ref_url Ödemenin gerçekleştiği sayfanın URLsi
     * @param  string|null  $Data1 Extra Alan 1
     * @param  string|null  $Data2 Extra Alan 2
     * @param  string|null  $Data3 Extra Alan 3
     * @param  string|null  $Data4 Extra Alan 4
     * @param  string|null  $Data5 Extra Alan 5
     *
     * @throws Exception
     */
    public function tp_islem_odeme_wd(
        int $doviz_kodu,
        string $kk_sahibi,
        string $kk_no,
        string $kk_sk_ay,
        string $kk_sk_yil,
        string $kk_cvc,
        string $hata_url,
        string $basarili_url,
        string $siparis_id,
        float $islem_tutar,
        float $toplam_tutar,
        string $islem_guvenlik_tip,
        string $ip_address,
        string $kk_sahibi_gsm = null,
        string $siparis_aciklama = null,
        string $islem_id = null,
        string $ref_url = null,
        string $Data1 = null,
        string $Data2 = null,
        string $Data3 = null,
        string $Data4 = null,
        string $Data5 = null
    ): array {
        $islem_guvenlik_tip = Str::upper($islem_guvenlik_tip);
        if (! in_array($islem_guvenlik_tip, ['NS', '3D'])) {
            throw new Exception('PARAM: TP_Islem_Odeme_WD: islem_guvenlik_tip parametresi NS veya 3D olmalıdır');
        }

        $islem_tutar = number_format($islem_tutar, 2, ',', '');
        $toplam_tutar = number_format($toplam_tutar, 2, ',', '');

        $islem_hash = $this->sha2b64(config('param-pos.client_code').config('param-pos.guid').$islem_tutar.$toplam_tutar.$siparis_id.$hata_url.$basarili_url);

        $res = $this->client->TP_Islem_Odeme_WD([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'Doviz_Kodu' => $doviz_kodu,
            'KK_Sahibi' => $kk_sahibi,
            'KK_No' => $kk_no,
            'KK_SK_Ay' => $kk_sk_ay,
            'KK_SK_Yil' => $kk_sk_yil,
            'KK_CVC' => $kk_cvc,
            'KK_Sahibi_GSM' => $kk_sahibi_gsm,
            'Hata_URL' => $hata_url,
            'Basarili_URL' => $basarili_url,
            'Siparis_ID' => $siparis_id,
            'Siparis_Aciklama' => $siparis_aciklama,
            'Islem_Tutar' => $islem_tutar,
            'Toplam_Tutar' => $toplam_tutar,
            'Islem_Hash' => $islem_hash,
            'Islem_Guvenlik_Tip' => $islem_guvenlik_tip,
            'Islem_ID' => $islem_id,
            'IPAdr' => $ip_address,
            'Ref_URL' => $ref_url,
            'Data1' => $Data1,
            'Data2' => $Data2,
            'Data3' => $Data3,
            'Data4' => $Data4,
            'Data5' => $Data5,
        ]);

        $res = $res->TP_Islem_Odeme_WDResult;

        if ($res->Sonuc < 0) {
            throw new Exception($res->Sonuc_Str);
        }

        if ($islem_guvenlik_tip == 'NS' && $res->UCD_URL !== 'NONSECURE') {
            throw new Exception('PARAM: TP_Islem_Odeme_WD: UCD_URL değeri NONSECURE olmalıdır');
        }

        return $this->res($res);
    }

    /**
     * BKM Express aracılığı ile ödeme işleminin başlatılacağı metottur.
     * İşlem sonucu dönen Redirect_URL parametresine yönlendirme yapılır ve BKM Express’e giriş yapılarak ödeme işlemi başlar.
     *
     * @link https://dev.param.com.tr/tr/api/bkm-express-ile-odeme #BKM Express ile Ödeme
     *
     * @param  string  $customer_gsm Üye İşyeri müşterisi GSM No, başında 0 olmadan (5xxxxxxxxx)
     * @param  string  $error_url Ödeme işlemi başarısız olursa yönlenecek sayfa adresi
     * @param  string  $success_url Ödeme işlemi başarılı olırsa yönlenecek sayfa adresi
     * @param  string  $order_id Siparişe özel tekil ID. Bu değeri daha önce gönderdiyseniz sistem yeni Siparis_ID atar. İşlem sonucunda bu Siparis_ID yi döner.
     * @param  float  $amount Sipariş Tutarı, (sadece virgüllü kuruş formatında 1000,50)
     * @param  string  $ip_address IP Adresi
     * @param  ?string  $customer_info Üye İşyeri müşterisi ad soyad/firma adı
     * @param  ?string  $order_description Siparişe ait açıklama
     * @param  ?string  $transaction_id İşleme ait Sipariş ID haricinde tekil ID, opsiyoneldir.
     * @param  ?string  $referral_url Ödemenin gerçekleştiği sayfanın URLsi.
     *
     * @throws Exception
     */
    public function tp_islem_odeme_bkm(
        string $customer_gsm,
        string $error_url,
        string $success_url,
        string $order_id,
        float $amount,
        string $ip_address,
        string $customer_info = null,
        string $order_description = null,
        string $transaction_id = null,
        string $referral_url = null
    ): array {
        $amount = number_format($amount, 2, ',', '');
        $hash = $this->sha2b64(
            config('param-pos.client_code').
            config('param-pos.guid').
            $amount.
            $order_id.
            $error_url.
            $success_url
        );

        $res = $this->client->TP_Islem_Odeme_BKM([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'Customer_Info' => $customer_info,
            'Customer_GSM' => $customer_gsm,
            'Error_URL' => $error_url,
            'Success_URL' => $success_url,
            'Order_ID' => $order_id,
            'Order_Description' => $order_description,
            'Amount' => $amount,
            'Payment_Hash' => $hash,
            'Transaction_ID' => $transaction_id,
            'IPAddress' => $ip_address,
            'Referrer_URL' => $referral_url,
        ]);

        $res = $res->TP_Islem_Odeme_BKMResult;

        if ($res->Response_Code < 0) {
            throw new Exception($res->Response_Message);
        }

        return $this->res($res);
    }

    /**
     * Şifreli veri üretmek için kullanılır.
     *
     * @link https://dev.param.com.tr/tr/api/sha2b64 #SHA2B64
     *
     * @param  string  $data Şifrelenecek Veri
     */
    public function sha2b64(
        string $data
    ): mixed {
        $res = $this->client->SHA2B64([
            'Data' => $data,
        ]);

        return $res->SHA2B64Result;
    }

    /**
     * Başarılı bir kredi kartı işleminin iptal veya iadesini yapmak için kullanılır.
     *
     * @link https://dev.param.com.tr/tr/api/islem-iptal-ve-iadeleri #İşlem İptal ve İadeleri
     *
     * @param  string  $durum IPTAL veya IADE
     * @param  string  $siparis_id İşlemin Sipariş ID Değeri
     * @param  float  $tutar İptal/İade Tutarı, IPTAL için tüm tutar yazılmalıdır. IADE için tüm tutar veya daha küçük tutar (kısmi) yazılmalıdır.
     *
     * @throws Exception
     */
    public function tp_islem_iptal_iade_kismi2(
        string $durum,
        string $siparis_id,
        float $tutar
    ): array {
        $durum = Str::upper($durum);
        if (! in_array($durum, ['IPTAL', 'IADE'])) {
            throw new Exception('PARAM: TP_Islem_Iptal_Iade_Kismi2: durum parametresi IPTAL veya IADE olmalıdır');
        }

        $res = $this->client->TP_Islem_Iptal_Iade_Kismi2([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'Durum' => $durum,
            'Siparis_ID' => $siparis_id,
            'Tutar' => $tutar,
        ]);

        $res = $res->TP_Islem_Iptal_Iade_Kismi2Result;

        if ($res->Sonuc !== '1') {
            throw new Exception($res->Sonuc_Str);
        }

        return $this->res($res);
    }

    /**
     * Firma tarafından üye işyerine özel verilmiş sanal pos oranları listelenir.
     * Üye işyeri bu oranlar üzerinde değişiklik yapabilir {@link https://dev.param.com.tr/tr/api/pos-oranlari?tab=kullanici-pos-oranlari #(Kullanıcı Pos Oranları)}.
     *
     * @link https://dev.param.com.tr/tr/api/pos-oranlari #Firma Pos Oranları
     *
     * @throws Exception
     */
    public function tp_ozel_oran_liste(): array
    {
        $res = $this->client->TP_Ozel_Oran_Liste([
            'G' => $this->G,
            'GUID' => $this->GUID,
        ]);

        $res = $res->TP_Ozel_Oran_ListeResult;

        if ($res->Sonuc !== '1') {
            throw new Exception($res->Sonuc_Str);
        }

        $diffgram = $this->diffgram($res);

        return $diffgram['NewDataSet']['DT_Ozel_Oranlar'];
    }

    /**
     * Özel oran son kullanıcı liste, standart olarak {@link https://dev.param.com.tr/tr/api/pos-oranlari #Firma Pos Oranları} deki metottan dönen oranların aynısı döner.
     * Üye işyerinin müşterisine göstereceği komisyon oranlarını listeler.
     *
     * Örneğin, firma Axess kredi kartı 3 taksit için verdiği komisyon oranı %3.15 olsun. Üye işyeri bu oranın müşterileri için fazla olduğunu düşünür ve TP_Ozel_Oran_SK_Guncelle deki metodu kullanarak bu komisyon oranını %2,50 olarak günceller. Müşteri, üye işyerinin sitesinde, ödeme aşamasında bu taksit komisyon oranını %2.50 olarak görür ve ödemeyi yapar. Firma, müşteri oranı %2,50’yi müşteriden, iki oran arasındaki %0,65’lik farkı ise üye işyerinden tahsil eder.
     *
     * @link https://dev.param.com.tr/tr/api/pos-oranlari #Kullanıcı Pos Oranları
     *
     * @throws Exception
     */
    public function tp_ozel_oran_sk_liste(): array
    {
        $res = $this->client->TP_Ozel_Oran_SK_Liste([
            'G' => $this->G,
            'GUID' => $this->GUID,
        ]);

        $res = $res->TP_Ozel_Oran_SK_ListeResult;

        if ($res->Sonuc !== '1') {
            throw new Exception($res->Sonuc_Str);
        }

        $diffgram = $this->diffgram($res);

        return $diffgram['NewDataSet']['DT_Ozel_Oranlar_SK'];
    }

    /**
     * Üye işyerinin, Firma tarafından verilen komisyon oranlarından daha düşük bir oranı müşterisine göstermesi içindir.
     * Bu komisyon oranı firmanın üye işyerine verdiği komisyon oranına küçük eşit olabilir.
     * İki komisyon oranı arasındaki fark “0” dan büyükse, bu farkı üye işyeri karşılar.
     *
     * (Örn. {@link https://dev.param.com.tr/tr/api/pos-oranlari?tab=kullanici-pos-oranlari #Kullanıcı Pos Oranları / 2.paragraf})
     *
     * Tüm parametreler zorunludur. Komisyon oranının aynı kalması istenen taksit parametresi için “100” değeri gönderilir.
     * Herhangi bir taksit seçeneği kullanılmasın isteniyorsa “-1” gönderilir.
     * Herhangi bir taksit oranı -1 veya -2 ise diğer bir deyişle “0” dan küçük olduğu durumlarda
     * bu taksit seçeneği o kart markası için kullanılamaz demektir.
     *
     * <div style="color: yellow">
     *     Yazılımcı Notu: Sadece değiştirmek istediğiniz taksit oranını göndermeniz yeterlidir. <br>
     *     Örn: 1 => 1.5, 2 => 2.5 gibi. bu sadece 1 ve 2 taksit oranlarını değiştirir. diğerleri tp_ozel_oran_sk_liste deki oranlarla aynı kalır.
     * </div>
     *
     * @link https://dev.param.com.tr/tr/api/pos-oranlari?tab=ozellestirilmis-pos-oranlari #Özelleştirilmiş Pos Oranları
     *
     * @param  string  $ozel_oran_sk_id Özel Oran SK ID
     * @param  array  $mo Taksit Oranları [ 1 => 1.15, 2 => 2.20] gibi
     *
     * @throws Exception
     */
    public function tp_ozel_oran_sk_guncelle(
        string $ozel_oran_sk_id,
        array $mo
    ): array {
        $rates = $this->tp_ozel_oran_sk_liste();
        // Ozel_Oran_ID si $ozel_oran_sk_id olan oranı bul
        $rate = array_filter($rates, fn ($rate) => $rate['Ozel_Oran_SK_ID'] == $ozel_oran_sk_id);

        if (count($rate) === 0) {
            throw new Exception('PARAM: TP_Ozel_Oran_SK_Guncelle: Ozel_Oran_ID bulunamadı');
        }

        $rate = array_values($rate)[0];
        $rateMos = array_filter($rate, fn ($val, $key) => Str::startsWith($key, 'MO_'), ARRAY_FILTER_USE_BOTH);
        $rateMosKeys = array_keys($rateMos);
        $rateMosValues = array_values($rateMos);

        // MO_01 => 1, MO_12 => 12
        $rateMos = array_combine(
            array_map(fn ($key) => (int) str_replace('MO_', '', $key), $rateMosKeys),
            array_map(fn ($val) => (float) $val, $rateMosValues)
        );

        // mo da olmayan taksit değerleri rateMos dan al
        foreach ($rateMos as $key => $val) {
            if (! isset($mo[$key])) {
                $mo[$key] = $val;
            }
        }

        $keys = array_keys($mo);
        // eğer key ler sayı değilse veya 1 ile 12 arasında değilse hata ver
        $check = array_filter($keys, fn ($key) => ! is_numeric($key) || $key < 1 || $key > count($rateMosKeys));
        if (count($check) > 0) {
            throw new Exception('PARAM: TP_Ozel_Oran_SK_Guncelle: taksit değerleri 1 ile '.count($rateMosKeys).' arasında ve sayısal değerler olmalıdır');
        }
        $values = array_values($mo);
        // değerler float değilse hata ver
        $check = array_filter($values, fn ($val) => ! is_numeric($val));
        if (count($check) > 0) {
            throw new Exception('PARAM: TP_Ozel_Oran_SK_Guncelle: taksit değerleri sayısal değerler olmalıdır');
        }

        $mo = array_combine(
            array_map(fn ($key) => "MO_$key", $keys),
            array_map(fn ($val) => number_format($val, 2, ',', ''), $values)
        );

        $res = $this->client->TP_Ozel_Oran_SK_Guncelle([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'Ozel_Oran_SK_ID' => $ozel_oran_sk_id,
            ...$mo,
        ]);

        $res = $res->TP_Ozel_Oran_SK_GuncelleResult;

        if ($res->Sonuc !== '1') {
            throw new Exception($res->Sonuc_Str);
        }

        return $this->res($res);
    }

    /**
     * Belirli tarihleri arasındaki üye işyerinin işlemleri özet biçiminde alabilirsiniz.
     *
     * @link https://dev.param.com.tr/tr/api/islem-ozetleri #İşlem Özetleri
     *
     * @param  Carbon  $startAt Başlangıç Tarihi
     * @param  Carbon  $endAt Bitiş Tarihi
     *
     * @throws Exception
     */
    public function tp_mutabakat_ozet(
        Carbon $startAt,
        Carbon $endAt
    ): array {
        $res = $this->client->TP_Mutabakat_Ozet([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'Tarih_Bas' => $startAt->format('d.m.Y H:i:s'),
            'Tarih_Bit' => $endAt->format('d.m.Y H:i:s'),
        ]);

        $res = $res->TP_Mutabakat_OzetResult;

        if ($res->Sonuc !== '1') {
            throw new Exception($res->Sonuc_Str);
        }

        $diffgram = $this->diffgram($res);

        return $diffgram['NewDataSet']['DT_Mutabakat_Ozet'];
    }

    /**
     * Belirli tarihte üye işyerinin mutabakat detaylarını alabilirsiniz.
     *
     * @link https://dev.param.com.tr/tr/api/islem-detaylari #Mutabakat Detay
     *
     * @param  Carbon  $date İşlem Tarihi
     *
     * @throws Exception
     */
    public function tp_mutabakat_detay(
        Carbon $date
    ): array {
        $res = $this->client->TP_Mutabakat_Detay([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'Tarih' => $date->format('d.m.Y H:i:s'),
        ]);

        $res = $res->TP_Mutabakat_DetayResult;

        if ($res->Sonuc !== '1') {
            throw new Exception($res->Sonuc_Str);
        }

        $diffgram = $this->diffgram($res);

        return $diffgram['NewDataSet']['DT_Mutabakat_Detay'];
    }

    /**
     * İşlemin başarılı, başarısız, iptal veya iade durumunda olduğunu öğrenebilirsiniz.
     *
     * TP_Islem_Odeme metodu sonrasında, kredi kartı 3D şifre bilgilerinin girilmesinin ardından
     * işlemin başarılı veya başarısız olma durumuna göre,
     * Basarili_URL veya Hata_URL’ye POST sırasında bir hata oluştuğunda da
     * bu metot ile işlemin durumunu sorgulayabilirsiniz.
     *
     * @link https://dev.param.com.tr/tr/api/islem-sorgulama #İşlem Sorgulama
     *
     * @param  ?string  $dekont_id Başarılı işlem sonrası POST edilen Dekont_ID
     * @param  ?string  $siparis_id Başarılı işlem sonrası POST edilen Sipariş ID
     * @param  ?string  $islem_id TP_Islem_Odeme metoduna gönderilen İşlem ID
     *
     * @throws Exception
     */
    public function tp_islem_sorgulama4(
        string $dekont_id = null,
        string $siparis_id = null,
        string $islem_id = null
    ): array {
        if (is_null($dekont_id) && is_null($siparis_id) && is_null($islem_id)) {
            throw new Exception('PARAM: TP_Islem_Sorgulama4: dekont_id, siparis_id veya islem_id den en az biri zorunludur');
        }
        $res = $this->client->TP_Islem_Sorgulama4([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'Dekont_ID' => $dekont_id,
            'Siparis_ID' => $siparis_id,
            'Islem_ID' => $islem_id,
        ]);

        $res = $res->TP_Islem_Sorgulama4Result;

        if ($res->Sonuc !== '1') {
            throw new Exception($res->Sonuc_Str);
        }

        return $this->res($res->DT_Bilgi);
    }

    /**
     * Yapılan işlemlerin belirli tarih aralığında izlenmesidir. İşlemlere ait bilgileri döner.
     *
     * Bilgi olarak ;
     * İşlem tipi,taksit sayısı,komisyon oran,komisyon tutar ,net tutar,tutar,dekont ID,ödeme yapan bilgisi,işlem güvenlik bilgilerine (NS (NonSecure) veya 3D) ulaşılır.
     *
     * @link https://dev.param.com.tr/tr/api/islem-izleme-metodu #İşlem İzleme Metodu
     *
     * @param  Carbon  $startAt Başlangıç Tarihi
     * @param  Carbon  $endAt Bitiş Tarihi
     * @param  ?string  $islem_tip İptal ,İade ,Satış Gönderilmediği taktirde hepsi döner süre uzar.
     * @param  ?string  $islem_durum Başarılı ,Başarısız Gönderilmediği taktirde hepsi döner süre uzar.
     *
     * @throws Exception
     */
    public function tp_islem_izleme(
        Carbon $startAt,
        Carbon $endAt,
        string $islem_tip = null,
        string $islem_durum = null
    ): array {
        $res = $this->client->TP_Islem_Izleme([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'Tarih_Bas' => $startAt->format('d.m.Y H:i:s'),
            'Tarih_Bit' => $endAt->format('d.m.Y H:i:s'),
            'Islem_Tip' => $islem_tip,
            'Islem_Durum' => $islem_durum,
        ]);

        $res = $res->TP_Islem_IzlemeResult;

        if ($res->Sonuc !== '1') {
            throw new Exception($res->Sonuc_Str);
        }

        $diffgram = $this->diffgram($res);

        return $diffgram['NewDataSet']['Temp'];
    }

    /**
     * Dekontun e-posta olarak gönderilmesini sağlar.
     *
     * @link https://dev.param.com.tr/tr/api/dekont #Dekont
     *
     * @param  string  $dekont_id İşlemin Dekont ID si
     * @param  string  $e_posta Kişinin E-Posta Adresi
     *
     * @throws Exception
     */
    public function tp_islem_dekont_gonder(
        string $dekont_id,
        string $e_posta
    ): array {
        $res = $this->client->TP_Islem_Dekont_Gonder([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'Dekont_ID' => $dekont_id,
            'E_Posta' => $e_posta,
        ]);

        $res = $res->TP_Islem_Dekont_GonderResult;

        if ($res->Sonuc !== '1') {
            throw new Exception($res->Sonuc_Str);
        }

        return $this->res($res);
    }

    /**
     * Kredi kartına ait kart-banka bilgisini ve SanalPOS_ID değerini döner.
     * BIN değerinin boş gönderilmesi durumunda bütün kayıtlar dönecektir.
     *
     * @link https://dev.param.com.tr/tr/api/bin-kodlari #Bin Kodları
     *
     * @param  ?int  $bin 6 ya da 8 sayı => Boş bırakılırsa tüm BIN kodları döner. Dolu gönderilirse o BIN koduna ait bilgiler döner.
     *
     * @throws Exception
     */
    public function bin_sanalpos(
        int $bin = null
    ): array {
        $res = $this->client->Bin_SanalPos([
            'G' => $this->G,
            'GUID' => $this->GUID,
            'BIN' => $bin,
        ]);

        $res = $res->BIN_SanalPosResult;

        if ($res->Sonuc !== '1') {
            throw new Exception($res->Sonuc_Str);
        }

        $diffgram = $this->diffgram($res);

        return $diffgram['NewDataSet']['Temp'];
    }

    /**
     * Kredi kartına ait toplam tutarı hesaplayıp döner.
     *
     * @param  string  $kk_no Kredi Kartı Numarası
     * @param  float  $amount İşlem Tutarı
     * @param  int  $taksit Taksit Sayısı
     * @return float Toplam Tutar
     *
     * @throws Exception
     */
    public function toplam_tutar_hesapla(
        string $kk_no,
        float $amount,
        int $taksit = 1
    ): float {
        $rate = $this->findRate($kk_no, $taksit);

        return $amount + ($amount * $rate / 100);
    }

    /**
     * Kredi kartına ait işlem tutarını hesaplayıp döner.
     *
     * @param  string  $kk_no Kredi Kartı Numarası
     * @param  float  $amount Toplam Tutar
     * @param  int  $taksit Taksit Sayısı
     * @return float İşlem Tutarı
     *
     * @throws Exception
     */
    public function islem_tutar_hesapla(
        string $kk_no,
        float $amount,
        int $taksit = 1
    ): float {
        $rate = $this->findRate($kk_no, $taksit);

        return $amount - ($amount * $rate / 100);
    }

    /**
     * Kredi kartına ait komisyon oranını döner.
     *
     * @param  string  $kk_no Kredi Kartı Numarası
     * @param  int  $taksit Taksit Sayısı
     * @return float Oran
     *
     * @throws Exception
     */
    private function findRate(
        string $kk_no,
        int $taksit
    ): float {
        $first6digits = substr($kk_no, 0, 6);
        $bin = $this->bin_sanalpos((int) $first6digits);
        $list = $this->tp_ozel_oran_liste();

        $rate = array_filter($list, fn ($i) => $i['SanalPOS_ID'] == $bin['SanalPOS_ID']);
        if (empty($rate)) {
            throw new Exception('PARAM: Toplam Tutar Hesapla: SanalPOS_ID bulunamadı');
        }

        $rate = array_values($rate)[0];
        $taksit = $taksit < 10 ? '0'.$taksit : $taksit;

        return $rate['MO_'.$taksit];
    }

    /**
     * Gelen Response daki DT_Bilgi->any bilgisindeki diffgr:diffgram ı array e çevirir.
     */
    private function diffgram($res): array
    {
        $res = $res->DT_Bilgi->any;
        preg_match('/<diffgr:diffgram.*?>(.*)<\/diffgr:diffgram>/s', $res, $matches);

        return $this->xmlToArray($matches[0]);
    }

    /**
     * XML i array e çevirir.
     */
    private function xmlToArray($xml): array
    {
        return json_decode(json_encode((array) simplexml_load_string($xml)), true);
    }

    /**
     * Objeyi array e çevirir.
     */
    private function res($obj): array
    {
        $res = json_decode(json_encode($obj), true);
        if (isset($res['Bank_Extra'])) {
            $res['Bank_Extra'] = $this->xmlToArray($res['Bank_Extra']);
        }

        return $res;
    }
}
