<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'welcome' => [
                'subject' => 'ยินดีต้อนรับเข้าสู่ระบบพอร์ทัล CARS-HUSOC',
                'body' => "เรียน {{name}}\n\nบัญชีของคุณได้รับการเปิดใช้งานอย่างเป็นทางการเรียบร้อยแล้ว ยินดีต้อนรับสู่ระบบพัฒนาและประเมินทักษะทางภาษาของศูนย์บริการวิชาการ CARS-HUSOC",
            ],
            'payment_approved' => [
                'subject' => 'แจ้งยืนยันการรับชำระเงินค่าบริการอย่างเป็นทางการ',
                'body' => "เรียน {{name}}\n\nสลิปโอนเงินของคุณสำหรับรายการ \"{{item_name}}\" ได้รับการยืนยันและอนุมัติสิทธิ์เรียบร้อยแล้ว ปัจจุบันสามารถเข้าใช้งานได้ทันที",
            ],
            'score_released' => [
                'subject' => 'ผลคะแนนสอบประเมินภาษาออกอย่างเป็นทางการแล้ว',
                'body' => "เรียน {{name}}\n\nผลคะแนนสอบรอบ \"{{exam_name}}\" ของคุณได้ถูกบันทึกและตรวจสอบเรียบร้อยแล้ว คะแนนรวมที่ได้รับคือ {{score}} คะแนน ระดับ CEFR: {{cefr_level}}\n\nสามารถเข้าดูและดาวน์โหลด Digital Certificate ได้ในระบบพอร์ทัลส่วนตัว",
            ],
            'translation_quote_sent' => [
                'subject' => 'ใบเสนอราคางานแปลเอกสารของคุณพร้อมแล้ว',
                'body' => "เรียน {{name}}\n\nศูนย์แปลเอกสารได้ประเมินราคาสำหรับงานของคุณเรียบร้อยแล้ว ราคาประเมิน {{price}} บาท กำหนดส่งมอบ {{delivery_date}}\n\nกรุณาชำระเงินในระบบเพื่อยืนยันการแปลเอกสาร",
            ],
            'translation_delivered' => [
                'subject' => 'งานแปลเอกสารของคุณเสร็จสมบูรณ์แล้ว',
                'body' => "เรียน {{name}}\n\nงานแปลเอกสาร \"{{file_name}}\" ของคุณเสร็จสมบูรณ์แล้ว สามารถเข้าสู่ระบบเพื่อดาวน์โหลดไฟล์งานแปลได้ทันที",
            ],
            'location_changed' => [
                'subject' => 'แจ้งเปลี่ยนแปลงสถานที่จัดกิจกรรม',
                'body' => "เรียน {{name}}\n\nสถานที่จัดกิจกรรมสำหรับ \"{{item_name}}\" มีการเปลี่ยนแปลง จากเดิม \"{{old_location}}\" เป็น \"{{new_location}}\" กรุณาตรวจสอบก่อนเข้าร่วมกิจกรรม",
            ],
        ];

        foreach ($templates as $key => $data) {
            EmailTemplate::firstOrCreate(['key' => $key], $data);
        }
    }
}
