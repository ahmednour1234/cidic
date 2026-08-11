# Image credits

## Client-owned assets (not stock)

`logo.png`, `logo-mark.png` and `favicon.png` are derived from the **CIDIC RECRUITMENT
logo supplied by the client** (`public/images/WhatsApp_Image_2026-08-07_at_17.27.01-removebg-preview.png`).
They were cropped to their bounding box and resized; colours are unaltered.

Brand palette sampled from that artwork: **#0060A8** (blue) and **#848484** (grey) —
these drive `--primary` and `--secondary` in `resources/css/app.css`.

## Stock photography

The remaining images are **stock photos downloaded from Pexels** and stored
locally (never hot-linked). They are placeholders for the client's own photography.

## Licence

Pexels Licence — <https://www.pexels.com/license/>

- Free for commercial and non-commercial use
- No attribution required (a credit is appreciated but not obligatory)
- May not be sold unaltered, and identifiable people may not be used in a way that
  is defamatory or implies endorsement

## Files

| File                      | Pexels photo ID | Used for                          |
|---------------------------|-----------------|-----------------------------------|
| `hero.jpg`                | 9462193         | Homepage hero + OpenGraph image   |
| `service-recruitment.jpg` | 9127761         | Service: الاستقدام                |
| `service-rental.jpg`      | 4239146         | Service: الإيجار الشهري           |
| `service-transfer.jpg`    | 7641003         | Service: نقل الخدمات              |
| `about.jpg`               | 3184418         | Spare (not currently referenced)  |

Source URL pattern: `https://www.pexels.com/photo/<ID>/`

## Replacing these

These are generic stock images, not Saudi-specific. Before launch, replace them with the
client's own photography via **لوحة التحكم → إعدادات الموقع → الصفحة الرئيسية** (hero) and
**الخدمات → تعديل** (per-service images). Uploaded files land in `storage/app/public/`
and the old file is deleted automatically.
