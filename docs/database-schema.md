# Mini LMS — Database Schema & Feature Specification (Full)

ระบบจัดการคอร์สเรียนออนไลน์ (Learning Management System) พัฒนาด้วย Laravel — เอกสารนี้ครอบคลุม schema ทุกตาราง, ความสัมพันธ์, enum, index และรายละเอียดฟีเจอร์ที่ผูกกับแต่ละตาราง

---

## 1. ภาพรวม Entity Relationship

```
users ──┬──< courses (instructor_id)
        ├──< enrollments >── courses
        ├──< reviews >── courses
        └──< certificates >── courses

courses ──< lessons ──< lesson_progress >── enrollments

enrollments ──< lesson_progress
enrollments ──< certificates (1:1 เมื่อเรียนจบ)

categories ──< courses
```

---

## 2. ตาราง `users`

| Column | Type | หมายเหตุ |
|---|---|---|
| id | bigint, PK | |
| name | varchar(255) | |
| email | varchar(255), unique | |
| email_verified_at | timestamp, nullable | |
| password | varchar(255) | hashed |
| role | enum('admin','instructor','student') | default 'student' |
| avatar | varchar(255), nullable | path ใน storage |
| remember_token | varchar(100), nullable | |
| created_at / updated_at | timestamp | |

**Index:** `email` (unique), `role` (สำหรับ query filter ตาม role บ่อย)

**Feature ที่เกี่ยวข้อง:**
- Register/Login พร้อม email verification (Laravel built-in)
- Password reset flow
- Middleware แยกสิทธิ์ตาม `role` (admin / instructor / student)
- Policy ตรวจสอบสิทธิ์ระดับ action (เช่น instructor แก้ไขได้เฉพาะคอร์สตัวเอง)

---

## 3. ตาราง `categories`

| Column | Type | หมายเหตุ |
|---|---|---|
| id | bigint, PK | |
| name | varchar(100) | |
| slug | varchar(100), unique | สำหรับ URL-friendly filter |
| created_at / updated_at | timestamp | |

**Feature ที่เกี่ยวข้อง:**
- ใช้ filter คอร์สหน้ารายการ (AJAX filter ตามหมวดหมู่)
- Admin จัดการหมวดหมู่ได้ (CRUD ง่ายๆ)

---

## 4. ตาราง `courses`

| Column | Type | หมายเหตุ |
|---|---|---|
| id | bigint, PK | |
| instructor_id | bigint, FK → users.id | |
| category_id | bigint, FK → categories.id | |
| title | varchar(255) | |
| slug | varchar(255), unique | |
| description | text | |
| cover_image | varchar(255), nullable | path ใน storage |
| price | decimal(10,2) | default 0 (รองรับคอร์สฟรี) |
| max_students | int, nullable | null = ไม่จำกัด |
| status | enum('draft','published','archived') | default 'draft' |
| created_at / updated_at | timestamp | |
| deleted_at | timestamp, nullable | soft delete |

**Index:** `slug` (unique), `status`, `category_id`, `instructor_id`

**Feature ที่เกี่ยวข้อง:**
- CRUD คอร์ส (Admin/Instructor) — อัปโหลดรูปปกผ่าน Laravel Storage ไม่เก็บ base64 ใน DB
- สถานะคอร์ส: `draft` (ยังไม่เปิดให้เห็น) → `published` (เปิดลงทะเบียน) → `archived` (ปิดรับ แต่ผู้เรียนเดิมยังเข้าถึงได้)
- ค้นหา + filter หน้ารายการคอร์ส (AJAX: ตาม `category_id`, ช่วงราคา, คำค้นหาใน `title`)
- ตรวจสอบ `max_students` ก่อนอนุญาตให้ลงทะเบียนใหม่ (business logic ใน Service layer)
- Soft delete — คอร์สที่ลบแล้วยังต้องดูประวัติได้ (ผู้เรียนที่เคยลงทะเบียนไม่ควรเห็นข้อมูลหายไปเฉยๆ)

---

## 5. ตาราง `lessons`

| Column | Type | หมายเหตุ |
|---|---|---|
| id | bigint, PK | |
| course_id | bigint, FK → courses.id | |
| title | varchar(255) | |
| video_url | varchar(255) | path/URL วิดีโอ |
| duration_seconds | int | ความยาวคลิปทั้งหมด (ใช้คำนวณ % progress) |
| order | int | ลำดับบทเรียนในคอร์ส |
| created_at / updated_at | timestamp | |

**Index:** `course_id` + `order` (composite, สำหรับ query เรียงลำดับบทเรียนเร็วขึ้น)

**Feature ที่เกี่ยวข้อง:**
- Instructor เพิ่ม/เรียงลำดับบทเรียนในคอร์ส (drag-and-drop reorder ใช้ AJAX อัปเดต `order`)
- แสดงรายการบทเรียนในหน้าคอร์ส พร้อมสถานะเรียนจบ/ยังไม่จบของผู้เรียนแต่ละคน

---

## 6. ตาราง `enrollments`

| Column | Type | หมายเหตุ |
|---|---|---|
| id | bigint, PK | |
| user_id | bigint, FK → users.id | |
| course_id | bigint, FK → courses.id | |
| status | enum('pending','active','completed','cancelled') | default 'active' |
| enrolled_at | timestamp | |
| completed_at | timestamp, nullable | เมื่อเรียนจบทุกบท |
| created_at / updated_at | timestamp | |
| deleted_at | timestamp, nullable | soft delete |

**Index:** unique composite (`user_id`, `course_id`) — กันลงทะเบียนซ้ำ, index แยกที่ `course_id` (สำหรับ query จำนวนผู้ลงทะเบียนต่อคอร์ส)

**Feature ที่เกี่ยวข้อง:**
- ปุ่ม "ลงทะเบียนเรียน" (AJAX, ไม่ reload หน้า) — validate ผ่าน `EnrollCourseRequest` + `CoursePolicy`
- กันลงทะเบียนซ้ำด้วย unique constraint ระดับ DB (ไม่พึ่งแค่ validate ฝั่งแอป)
- กันลงทะเบียนเมื่อ `max_students` เต็ม (เช็คใน `EnrollmentService` ก่อน insert)
- Dashboard ผู้เรียน: แสดงคอร์สทั้งหมดที่ลงทะเบียนไว้ พร้อมสถานะ
- เมื่อทุก lesson ใน course มี `is_completed = true` → auto update `status = completed`, `completed_at = now()` (ทำผ่าน Event/Listener)

---

## 7. ตาราง `lesson_progress`

| Column | Type | หมายเหตุ |
|---|---|---|
| id | bigint, PK | |
| enrollment_id | bigint, FK → enrollments.id | |
| lesson_id | bigint, FK → lessons.id | |
| watched_seconds | int | default 0, เวลาที่ดูสะสม (ไม่ลดลงแม้ย้อนดู) |
| watched_percentage | int | คำนวณจาก watched_seconds / lesson.duration_seconds |
| is_completed | boolean | default false, true เมื่อถึง threshold (90%) |
| completed_at | timestamp, nullable | |
| created_at / updated_at | timestamp | |

**Index:** unique composite (`enrollment_id`, `lesson_id`)

**Feature ที่เกี่ยวข้อง (Progress Tracking ตามเวลาดูจริง):**
- Frontend ฟัง event `timeupdate` ของ `<video>`, throttle ส่ง AJAX ทุก 10 วินาที ไปอัปเดต `watched_seconds`
- Backend คำนวณ `watched_percentage` ทุกครั้งที่อัปเดต, mark `is_completed = true` เมื่อ ≥ 90% ของ `duration_seconds`
- **กัน watched_seconds ลดลง** เมื่อผู้เรียนย้อนกลับไปดูซ้ำ (`max(current, new)` ใน Service layer)
- **Resume playback**: โหลดหน้า lesson ครั้งถัดไป → set `video.currentTime = watched_seconds` เดิม
- Progress bar ของทั้งคอร์ส = `(COUNT(is_completed = true) / COUNT(lessons)) × 100` — คำนวณผ่าน `LessonProgressService::getCourseProgressPercentage()`

---

## 8. ตาราง `reviews` (Should-have)

| Column | Type | หมายเหตุ |
|---|---|---|
| id | bigint, PK | |
| user_id | bigint, FK → users.id | |
| course_id | bigint, FK → courses.id | |
| rating | tinyint | 1-5 |
| comment | text, nullable | |
| created_at / updated_at | timestamp | |

**Index:** unique composite (`user_id`, `course_id`) — รีวิวได้ครั้งเดียวต่อคอร์ส, index ที่ `course_id`

**Feature ที่เกี่ยวข้อง:**
- ผู้เรียนรีวิวได้เฉพาะคอร์สที่ตัวเอง `enrollment.status = completed` เท่านั้น (validate ใน Policy)
- แสดงค่าเฉลี่ย rating หน้ารายละเอียดคอร์ส (query aggregate `AVG(rating)`)

---

## 9. ตาราง `certificates` (Nice-to-have)

| Column | Type | หมายเหตุ |
|---|---|---|
| id | bigint, PK | |
| enrollment_id | bigint, FK → enrollments.id, unique | |
| certificate_number | varchar(50), unique | เช่น CERT-2026-000123 |
| file_path | varchar(255) | PDF ที่ generate ไว้ |
| issued_at | timestamp | |

**Feature ที่เกี่ยวข้อง:**
- Event `EnrollmentCompleted` → Listener สร้าง PDF certificate อัตโนมัติ (ใช้ package เช่น `barryvdh/laravel-dompdf`)
- ทำงานผ่าน Queue Job เพื่อไม่ block request หลัก (generate PDF ใช้เวลา)

---

## 10. ตาราง `bookmarks` (Nice-to-have)

| Column | Type | หมายเหตุ |
|---|---|---|
| id | bigint, PK | |
| user_id | bigint, FK → users.id | |
| course_id | bigint, FK → courses.id | |
| created_at | timestamp | |

**Index:** unique composite (`user_id`, `course_id`)

**Feature ที่เกี่ยวข้อง:**
- ปุ่ม bookmark/wishlist toggle ผ่าน AJAX (POST ถ้ายังไม่มี, DELETE ถ้ามีอยู่แล้ว)

---

## 11. Eloquent Relationships สรุป

```php
// User
public function coursesAsInstructor() { return $this->hasMany(Course::class, 'instructor_id'); }
public function enrollments()        { return $this->hasMany(Enrollment::class); }
public function courses()            { return $this->belongsToMany(Course::class, 'enrollments'); }

// Course
public function instructor()  { return $this->belongsTo(User::class, 'instructor_id'); }
public function category()    { return $this->belongsTo(Category::class); }
public function lessons()     { return $this->hasMany(Lesson::class)->orderBy('order'); }
public function enrollments() { return $this->hasMany(Enrollment::class); }
public function reviews()     { return $this->hasMany(Review::class); }

// Enrollment
public function user()     { return $this->belongsTo(User::class); }
public function course()   { return $this->belongsTo(Course::class); }
public function progress() { return $this->hasMany(LessonProgress::class); }

// Lesson
public function course()   { return $this->belongsTo(Course::class); }
public function progress() { return $this->hasMany(LessonProgress::class); }
```

---

## 12. Business Rules สรุป (ใช้อ้างอิงตอนเขียน Service/Policy)

| กติกา | บังคับที่ชั้นไหน |
|---|---|
| ห้ามลงทะเบียนคอร์สเดียวกันซ้ำ | DB unique constraint + validate ใน `EnrollCourseRequest` |
| ห้ามลงทะเบียนเมื่อ `max_students` เต็ม | `EnrollmentService` ตรวจก่อน insert |
| Instructor แก้ไขได้เฉพาะคอร์สตัวเอง | `CoursePolicy::update()` |
| Lesson นับว่าเรียนจบเมื่อดู ≥ 90% | `LessonProgressService` |
| `watched_seconds` ห้ามลดลงจากค่าที่เคยบันทึก | `LessonProgressService::updateProgress()` ใช้ `max()` |
| รีวิวได้เฉพาะคอร์สที่เรียนจบแล้ว | `ReviewPolicy::create()` เช็ค `enrollment.status = completed` |
| Course สถานะ `archived` ห้ามลงทะเบียนใหม่ แต่ผู้เรียนเดิมเข้าถึงได้ | `EnrollmentService` เช็ค `course.status` |

---

## 13. Index สรุปทั้งหมด (สำหรับ performance)

- `users`: `email` (unique), `role`
- `courses`: `slug` (unique), `status`, `category_id`, `instructor_id`
- `lessons`: composite (`course_id`, `order`)
- `enrollments`: composite unique (`user_id`, `course_id`), `course_id`
- `lesson_progress`: composite unique (`enrollment_id`, `lesson_id`)
- `reviews`: composite unique (`user_id`, `course_id`), `course_id`
- `bookmarks`: composite unique (`user_id`, `course_id`)

---

## 14. Feature Roadmap สรุป (อ้างอิงจาก priority ที่ตกลงกันไว้)

**Must-have:** Auth (3 roles), Course CRUD, Course discovery + AJAX filter, Enrollment system, Progress tracking ตามเวลาดูคลิปจริง

**Should-have:** Admin analytics dashboard (SQL aggregate), Export รายชื่อผู้เรียน (Excel/CSV), Email notification ผ่าน Queue, Review/Rating

**Nice-to-have:** Certificate PDF อัตโนมัติ, Bookmark/Wishlist, เชื่อม WordPress blog ผ่าน WP REST API

---

## 15. ขั้นตอนถัดไปที่แนะนำ

1. เขียน Migration ทุกตารางตามลำดับ (users → categories → courses → lessons → enrollments → lesson_progress → reviews/certificates/bookmarks)
2. เขียน Factory + Seeder สำหรับข้อมูลทดสอบ
3. เขียน Model + Relationship ตามข้อ 11
4. เขียน Policy สำหรับ Course, Enrollment, Review
5. เขียน Service layer (`CourseService`, `EnrollmentService`, `LessonProgressService`)
6. เขียน Form Request สำหรับทุก endpoint ที่รับ input จากผู้ใช้
7. ค่อยไปทำ Controller + Route (API และ Web แยกกัน)
