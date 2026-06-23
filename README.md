# 💈 Barbershop Management System (Backend API)
> **مشروع تخرج نظام إدارة صالون الحلاقة المتكامل - الباك اند**
>
> **إعداد المهندس/ محمد سلامة (Prepared by Eng. Mohamed Salama)**

---

## 📝 وصف المشروع (Project Description)

هذا المستودع (Repository) يمثل **الجهة الخلفية (Backend API)** لمشروع التخرج الخاص بنظام إدارة صالون الحلاقة (Barbershop Management System). تم بناء وتطوير النظام باستخدام إطار عمل **Laravel (PHP)** لتقديم خدمات الـ API بشكل سريع وآمن، وربطها بالواجهة الأمامية (Frontend) لإدارة الحجوزات، المبيعات، الحلاقين، والورديات.

This repository contains the **Backend API** for the Barbershop Management System graduation project. Developed with **Laravel**, it provides a secure, robust, and highly efficient RESTful API to power the system's frontend, handling appointments, billing, barber shifts, and dashboard analytics.

---

## 🚀 الميزات الرئيسية (Key Features)

* **📅 إدارة المواعيد (Appointments)**:
  * حجز المواعيد أونلاين أو يدوياً من الإدارة.
  * التحقق الذكي من رقم الهاتف وتسجيل العملاء الجدد تلقائياً.
  * فلترة الاستجابة تلقائياً لحذف بيانات قاعدة البيانات الزائدة (مثل التواريخ وعناوين الصور وحقول pivot).

* **🧾 نظام الفواتير التلقائي (Billing & Invoices)**:
  * إنشاء الفاتورة وعناصرها تلقائياً بمجرد تحويل حالة الحجز إلى مكتمل (`completed`).
  * حساب سعر كل خدمة وإجمالي الفاتورة تلقائياً بناءً على أسعار الخدمات الفعلية في قاعدة البيانات.

* **⏰ إدارة الورديات (Shift Management)**:
  * فتح وإغلاق الورديات بشكل مرن.
  * الحساب التلقائي والذكي لإحصائيات الوردية (إجمالي الإيرادات `total_revenue` وإجمالي عدد الطلبات `total_orders`) بناءً على المبيعات التي تمت خلال فترة الوردية فقط فور إغلاقها.
  * الربط التلقائي للفواتير والمواعيد بالشيفت المفتوح حالياً دون تدخل يدوي.

* **📊 لوحة التحكم والتقارير (Dashboard Stats)**:
  * مسار موحد لإحصائيات لوحة التحكم (`/api/dashboard/stats`) يُرجع كافة البيانات دفعة واحدة لسرعة التحميل.
  * جلب أفضل 5 عملاء زيارةً وتكراراً، وأفضل 5 عملاء دفعاً.
  * آخر 10 عمليات حلاقة مكتملة بالتفاصيل، وأحدث 3 ورديات.
  * الخدمات الخمس الأكثر طلباً (الأكثر شعبية) بناءً على الحجوزات المكتملة.
  * توفير بطاقات إحصائية للمربعات الأربعة العلوية (إجمالي العملاء، إجمالي الخدمات، إجمالي الحلاقين، وإجمالي رواتبهم).

---

## 🛠️ التقنيات المستخدمة (Tech Stack)

* **إطار العمل الرئيسي**: Laravel (PHP)
* **قاعدة البيانات**: MySQL / SQLite (للاختبارات)
* **بيئة الاختبار**: PHPUnit (Automated Feature Tests)

---

## ⚙️ التثبيت والتشغيل (Installation & Setup)

للبدء في تشغيل المشروع محلياً، يرجى اتباع الخطوات التالية:

1. **نسخ المشروع (Clone the repository)**:
   ```bash
   git clone <repo-url>
   cd "Barbershop Management System"
   ```

2. **تثبيت الملحقات (Install dependencies)**:
   ```bash
   composer install
   ```

3. **إعداد ملف البيئة (Configure environment file)**:
   - قم بنسخ ملف `.env.example` وتسميته بـ `.env`.
   - قم بتهيئة بيانات الاتصال بقاعدة البيانات الخاصة بك داخل ملف `.env`.

4. **إنشاء مفتاح التشفير (Generate Application Key)**:
   ```bash
   php artisan key:generate
   ```

5. **تهيئة قاعدة البيانات وتنفيذ التهجير (Run Migrations & Seeding)**:
   ```bash
   php artisan migrate --seed
   ```

6. **تشغيل الخادم المحلي (Run the server)**:
   ```bash
   php artisan serve
   ```

---

## 🧪 الاختبارات الآلية (Automated Testing)

يحتوي النظام على اختبارات آلية تغطي كافة المسارات الحساسة مثل المواعيد، الفواتير، وحساب الإيرادات التلقائي. لتشغيل الاختبارات:

```bash
php artisan test
```

---



### 👨‍💻 مطور المشروع (Developer)
* **المهندس**: محمد سلامة (Eng. Mohamed Salama)
* **المسمى الوظيفي**: Full-Stack Web Developer
* **الهدف**: مشروع تخرج متكامل لمسار الفل ستاك ويب.
