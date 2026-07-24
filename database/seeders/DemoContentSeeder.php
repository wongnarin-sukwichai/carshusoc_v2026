<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ScoreScale;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $scale = ScoreScale::firstOrCreate(
            ['name' => 'MSU-EPT Scale v1'],
            ['version' => 1, 'is_active' => true, 'effective_from' => now()->subYear()]
        );

        // The TOEIC/CEFR/EPT comparison table from the request.
        $bands = [
            ['cefr_level' => 'C2', 'toeic_min' => 975, 'toeic_max' => 990, 'ept_min' => 98, 'ept_max' => 100, 'sort_order' => 1],
            ['cefr_level' => 'C1', 'toeic_min' => 945, 'toeic_max' => 965, 'ept_min' => 96, 'ept_max' => 97, 'sort_order' => 2],
            ['cefr_level' => 'B2', 'toeic_min' => 785, 'toeic_max' => 940, 'ept_min' => 68, 'ept_max' => 95, 'sort_order' => 3],
            ['cefr_level' => 'B1', 'toeic_min' => 550, 'toeic_max' => 665, 'ept_min' => 56, 'ept_max' => 67, 'sort_order' => 4],
            ['cefr_level' => 'A2', 'toeic_min' => 225, 'toeic_max' => 545, 'ept_min' => 23, 'ept_max' => 55, 'sort_order' => 5],
            ['cefr_level' => 'A1', 'toeic_min' => 120, 'toeic_max' => 170, 'ept_min' => 12, 'ept_max' => 17, 'sort_order' => 6],
        ];

        foreach ($bands as $band) {
            $scale->bands()->firstOrCreate(['cefr_level' => $band['cefr_level']], $band);
        }

        $examTemplate = CertificateTemplate::firstOrCreate(
            ['service_center_code' => 'exam', 'name' => 'MSU-EPT Score Report'],
            [
                'title' => 'Mahasarakham University English Proficiency Test (MSU-EPT)',
                'subtitle' => 'ใบรายงานผลการทดสอบวัดความรู้ความสามารถด้านภาษาอังกฤษ',
                'signatory1_name' => 'ผู้ช่วยศาสตราจารย์ ดร.พรทิพย์ สุพรรณฝ่าย',
                'signatory1_title' => 'ประธานศูนย์ทดสอบภาษาอังกฤษ',
                'signatory2_name' => 'รองศาสตราจารย์ ดร.ธีระ รุ่งธีระ',
                'signatory2_title' => 'รองคณบดีฝ่ายวิจัยและบัณฑิตศึกษา',
                'signatory3_name' => 'รองศาสตราจารย์ ดร.กิตติพงษ์ ประพันธ์',
                'signatory3_title' => 'คณบดีคณะมนุษยศาสตร์และสังคมศาสตร์',
                'border_color' => '#4f2d7f',
                'is_default' => true,
            ]
        );

        $trainingTemplate = CertificateTemplate::firstOrCreate(
            ['service_center_code' => 'training', 'name' => 'ประกาศนียบัตรอบรมภาษา'],
            [
                'title' => 'ประกาศนียบัตร',
                'subtitle' => 'ได้ "ผ่าน" โครงการอบรมทักษะภาษาสำหรับนิสิตระดับบัณฑิตศึกษา',
                'signatory1_name' => 'รองศาสตราจารย์ ดร.ธีระ รุ่งธีระ',
                'signatory1_title' => 'รองคณบดีฝ่ายวิจัยและบัณฑิตศึกษา',
                'signatory2_name' => 'รองศาสตราจารย์ ดร.กิตติพงษ์ ประพันธ์',
                'signatory2_title' => 'คณบดีคณะมนุษยศาสตร์และสังคมศาสตร์',
                'border_color' => '#c9962c',
                'is_default' => true,
            ]
        );

        $en01 = Course::firstOrCreate(
            ['code' => 'EN-01'],
            [
                'name_th' => 'ภาษาอังกฤษเพื่อการสื่อสาร ระดับ 1',
                'name_en' => 'English Communication Level 1',
                'language' => 'อังกฤษ',
                'level' => 1,
                'price' => 2000,
                'is_visible' => true,
                'certificate_template_id' => $trainingTemplate->id,
            ]
        );

        Course::firstOrCreate(
            ['code' => 'EN-02'],
            [
                'name_th' => 'ภาษาอังกฤษเพื่อการสื่อสาร ระดับ 2',
                'name_en' => 'English Communication Level 2',
                'language' => 'อังกฤษ',
                'level' => 2,
                'price' => 2500,
                'prerequisite_course_id' => $en01->id,
                'is_visible' => true,
                'certificate_template_id' => $trainingTemplate->id,
            ]
        );

        Exam::firstOrCreate(
            ['code' => 'EX-EPT'],
            [
                'type' => 'EPT',
                'name_th' => 'การทดสอบวัดความรู้ความสามารถด้านภาษาอังกฤษ (MSU-EPT)',
                'name_en' => 'MSU English Proficiency Test',
                'price' => 800,
                'exam_date' => now()->addMonth()->toDateString(),
                'location' => 'คณะมนุษยศาสตร์และสังคมศาสตร์',
                'mail_delivery_available' => true,
                'mail_delivery_fee' => 50,
                'is_visible' => true,
                'certificate_template_id' => $examTemplate->id,
                'score_scale_id' => $scale->id,
            ]
        );
    }
}
