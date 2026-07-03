# MF Downloadable Gallery

A WordPress plugin for a category-based downloadable file gallery with cover images and optional email (lead) capture. Runs entirely through shortcodes with **zero external dependencies**.

**English** · [Türkçe](#türkçe)

- **Version:** 1.0.0
- **Requires WordPress:** 5.6+
- **Tested up to:** 6.6
- **Requires PHP:** 7.2+
- **License:** GPL-2.0-or-later

## Overview

MF Downloadable Gallery lists the PDFs and documents that promote your school, organization, or services alongside their cover images. Optionally, it collects the visitor's email address before the download (lead capture). Collected emails are listed in the admin panel and can be exported as CSV.

## Features

- 📁 Files are uploaded to the WordPress Media Library; no external storage needed.
- 🔒 Download method is set per file: **email-gated** or **direct**.
- 📧 Collected emails are listed in the admin panel and **exported as CSV** (all or unique emails).
- 🗂️ Hierarchical categories (`mfdg_category`) for files.
- 🔎 Front-end **AJAX category filter bar** to filter by category.
- 🖼️ **3:4 cover image** (featured image) for each file.
- 🧱 Grid layout (by upload order) or grouped-by-category layout.
- ✅ Configurable consent checkbox for KVKK/GDPR.
- 🌍 Full i18n support, **Turkish translation included** (`tr_TR`).
- 🪶 No external dependencies; pure PHP, CSS, and vanilla JS.

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`, or upload the zip via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin. On activation the leads table is created and default settings are written.
3. Add files from the **Downloads** menu: title, file, cover image (featured image), and the email-requirement setting.
4. Add the `[mf_downloadable_gallery]` shortcode to a page.

## Usage

### Shortcode

```
[mf_downloadable_gallery]
```

Lists all files as a grid, newest first, with the category filter bar.

### Parameters

| Parameter  | Values | Description |
|------------|--------|-------------|
| `layout`   | `grid` (default), `grouped` | Grid or grouped-by-category layout |
| `category` | slug | Category slug (comma-separated for multiple) |
| `columns`  | `1`–`6` | Number of columns |
| `orderby`  | `date`, `title`, `menu_order` | Order criterion |
| `order`    | `ASC`, `DESC` | Order direction |
| `filter`   | `yes` (default), `no` | Show/hide the filter bar |
| `limit`    | number | Number of files to show (`-1` = all) |
| `ids`      | ID list | Specific file IDs (comma-separated) |

### Examples

```
[mf_downloadable_gallery layout="grouped"]
[mf_downloadable_gallery category="brosurler" columns="4"]
[mf_downloadable_gallery order="ASC" filter="no"]
```

## Settings

Configurable under **Downloads → Settings**:

- Default email requirement (`default_require_email`)
- Consent checkbox toggle and text
- Download and gated button labels
- Success message
- Name collection (`collect_name`)
- Remember visitor (`remember_visitor`)
- Admin email notification for new leads (`notify_admin`, `notify_email`)
- Default column count (1–6)

## Structure

```
mf-downloadable-gallery.php     Plugin entry point, constants, activation/deactivation
includes/
  class-mfdg-cpt.php            Custom post type (mfdg_file) and taxonomy (mfdg_category)
  class-mfdg-leads.php          Leads table and CSV export
  class-mfdg-settings.php       Settings defaults, save, sanitization
  class-mfdg-metabox.php        File meta boxes
  class-mfdg-admin.php          Admin UI and leads list
  class-mfdg-frontend.php       Shortcode and front-end output
  class-mfdg-ajax.php           AJAX filtering and download flow
  class-mfdg-plugin.php         Main loader (singleton)
assets/
  css/  js/                     Admin and front-end assets
languages/                      .pot / tr_TR translation files
```

## License

[GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html) — © [mfdsgn](https://mfdsgn.com/)

---

# Türkçe

Kapak görselli, kategori bazlı, e-posta ile kayıt (lead) toplayan indirilebilir dosya galerisi WordPress eklentisi. Kısa kodlarla çalışır, **dış bağımlılığı yoktur**.

[English](#mf-downloadable-gallery) · **Türkçe**

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
