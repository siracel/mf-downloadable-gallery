# MF Downloadable Gallery

Kapak görselli, kategori bazlı, e-posta ile kayıt (lead) toplayan indirilebilir dosya galerisi WordPress eklentisi. Kısa kodlarla çalışır, **dış bağımlılığı yoktur**.

- **Sürüm:** 1.0.0
- **Gerekli WordPress:** 5.6+
- **Test edilen:** 6.6
- **Gerekli PHP:** 7.2+
- **Lisans:** GPL-2.0-or-later

## Genel Bakış

MF Downloadable Gallery; okulunuzu, kurumunuzu veya hizmetlerinizi tanıtan PDF ve belgeleri kapak görselleriyle birlikte listeler. İsteğe bağlı olarak, kullanıcı dosyayı indirmeden önce e-posta adresini toplar (lead capture). Toplanan e-postalar yönetici panelinde listelenir ve CSV olarak dışa aktarılabilir.

## Özellikler

- 📁 Dosyalar WordPress Ortam Kitaplığına yüklenir; harici depolama gerekmez.
- 🔒 Her dosya için indirme yöntemi tek tek belirlenir: **e-posta ile (gated)** veya **doğrudan**.
- 📧 Toplanan e-postalar yönetici panelinde listelenir; **CSV olarak dışa aktarılır** (tümü veya benzersiz e-postalar).
- 🗂️ Dosyalar için hiyerarşik kategoriler (`mfdg_category`) oluşturulabilir.
- 🔎 Ön yüzde **AJAX kategori filtre çubuğu** ile kategoriye göre filtreleme.
- 🖼️ Her dosya için **3:4 oranında kapak görseli** (öne çıkan görsel).
- 🧱 Izgara (yükleme sırasına göre) veya kategoriye göre gruplu düzen.
- ✅ KVKK/GDPR için ayarlanabilir onay kutusu (consent).
- 🌍 Tam i18n desteği, **Türkçe çeviri dahil** (`tr_TR`).
- 🪶 Dış bağımlılık yok; salt PHP, CSS ve vanilla JS.

## Kurulum

1. Eklenti klasörünü `/wp-content/plugins/` dizinine yükleyin veya **Eklentiler → Yeni Ekle → Eklenti Yükle** üzerinden zip dosyasını yükleyin.
2. Eklentiyi etkinleştirin. Etkinleştirmede lead tablosu oluşturulur ve varsayılan ayarlar yazılır.
3. **İndirmeler** menüsünden dosya ekleyin: başlık, dosya, kapak görseli (öne çıkan görsel) ve e-posta gerekliliği ayarını girin.
4. Bir sayfaya `[mf_downloadable_gallery]` kısa kodunu ekleyin.

## Kullanım

### Kısa kod

```
[mf_downloadable_gallery]
```

Tüm dosyaları ızgara olarak, en yeni önce, kategori filtre çubuğuyla listeler.

### Parametreler

| Parametre  | Değer | Açıklama |
|------------|-------|----------|
| `layout`   | `grid` (varsayılan), `grouped` | Izgara veya kategoriye göre gruplu düzen |
| `category` | slug | Kategori slug'ı (virgülle birden fazla) |
| `columns`  | `1`–`6` | Sütun sayısı |
| `orderby`  | `date`, `title`, `menu_order` | Sıralama ölçütü |
| `order`    | `ASC`, `DESC` | Sıralama yönü |
| `filter`   | `yes` (varsayılan), `no` | Filtre çubuğunu göster/gizle |
| `limit`    | sayı | Gösterilecek dosya sayısı (`-1` = tümü) |
| `ids`      | ID listesi | Belirli dosya ID'leri (virgülle) |

### Örnekler

```
[mf_downloadable_gallery layout="grouped"]
[mf_downloadable_gallery category="brosurler" columns="4"]
[mf_downloadable_gallery order="ASC" filter="no"]
```

## Ayarlar

**İndirmeler → Ayarlar** altından yapılandırılabilir:

- Varsayılan e-posta gerekliliği (`default_require_email`)
- Onay kutusu (consent) etkinliği ve metni
- İndirme butonu ve gated buton etiketleri
- Başarı mesajı
- İsim toplama (`collect_name`)
- Ziyaretçiyi hatırlama (`remember_visitor`)
- Yeni lead için yöneticiye e-posta bildirimi (`notify_admin`, `notify_email`)
- Varsayılan sütun sayısı (1–6)

## Yapı

```
mf-downloadable-gallery.php     Eklenti giriş noktası, sabitler, aktivasyon/deaktivasyon
includes/
  class-mfdg-cpt.php            Özel yazı tipi (mfdg_file) ve taksonomi (mfdg_category)
  class-mfdg-leads.php          Lead tablosu ve CSV dışa aktarma
  class-mfdg-settings.php       Ayarların varsayılanları, kaydı, temizliği
  class-mfdg-metabox.php        Dosya meta kutuları
  class-mfdg-admin.php          Yönetici arayüzü ve lead listesi
  class-mfdg-frontend.php       Kısa kod ve ön yüz çıktısı
  class-mfdg-ajax.php           AJAX filtreleme ve indirme akışı
  class-mfdg-plugin.php         Ana yükleyici (singleton)
assets/
  css/  js/                     Yönetici ve ön yüz varlıkları
languages/                      .pot / tr_TR çeviri dosyaları
```

## Lisans

[GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html) — © [mfdsgn](https://mfdsgn.com/)
