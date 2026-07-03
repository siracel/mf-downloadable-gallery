=== MF Downloadable Gallery ===
Contributors: mfdsgn
Tags: downloads, pdf, lead generation, gallery, brochures
Requires at least: 5.6
Tested up to: 6.6
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Kapak görselli, kategori bazlı, e-posta ile kayıt (lead) toplayan indirilebilir dosya galerisi.

== Description ==

MF Downloadable Gallery, okulunuzu/hizmetlerinizi tanıtan PDF ve belgeleri
kapak görselleriyle birlikte listeleyen, isteğe bağlı olarak e-posta ile
indirme (lead capture) sağlayan bir WordPress eklentisidir. Dış bağımlılığı yoktur.

Özellikler:

* Dosyalar WordPress Ortam Kitaplığına yüklenir.
* Her dosya için, e-posta ile mi yoksa doğrudan mı indirileceği tek tek belirlenir.
* Toplanan e-postalar yönetici panelinde listelenir ve CSV olarak dışa aktarılır
  (tümü veya benzersiz e-postalar).
* Dosyalar için hiyerarşik kategoriler oluşturulabilir.
* Ön yüzde kategori filtre çubuğu (AJAX) ile kategoriye göre filtreleme.
* Her dosya için 3:4 oranında kapak görseli (öne çıkan görsel).
* Izgara (yükleme sırasına göre) veya kategoriye göre gruplu düzen.
* KVKK/GDPR için ayarlanabilir onay kutusu.
* Türkçe çeviri dahil (tr_TR), tam i18n desteği.

== Installation ==

1. Eklenti klasörünü `/wp-content/plugins/` dizinine yükleyin veya
   Eklentiler > Yeni Ekle > Eklenti Yükle üzerinden zip dosyasını yükleyin.
2. Eklentiyi etkinleştirin.
3. "İndirmeler" menüsünden dosya ekleyin: başlık, dosya, kapak görseli (öne çıkan
   görsel) ve e-posta gerekliliği ayarını girin.
4. Bir sayfaya `[mf_downloadable_gallery]` kısa kodunu ekleyin.

== Shortcodes ==

`[mf_downloadable_gallery]`
Tüm dosyalar ızgara olarak, en yeni önce, kategori filtre çubuğuyla.

Parametreler:
* `layout`   : `grid` (varsayılan) veya `grouped` (kategoriye göre gruplu)
* `category` : kategori slug'ı (virgülle birden fazla)
* `columns`  : sütun sayısı (1–6)
* `orderby`  : `date` | `title` | `menu_order`
* `order`    : `ASC` | `DESC`
* `filter`   : `yes` (varsayılan) | `no` (filtre çubuğunu gizle)
* `limit`    : gösterilecek dosya sayısı (-1 = tümü)
* `ids`      : belirli dosya ID'leri (virgülle)

Örnekler:
`[mf_downloadable_gallery layout="grouped"]`
`[mf_downloadable_gallery category="brosurler" columns="4"]`
`[mf_downloadable_gallery order="ASC" filter="no"]`

== Changelog ==

= 1.0.0 =
* İlk sürüm.
