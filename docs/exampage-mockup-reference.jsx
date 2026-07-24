import React, { useState, useEffect } from 'react';
import { 
  BookOpen, 
  GraduationCap, 
  FileText, 
  CheckCircle, 
  Upload, 
  Download, 
  Search, 
  ArrowRight, 
  Lock, 
  AlertCircle, 
  User, 
  Settings, 
  Bell, 
  DollarSign, 
  Mail, 
  Clock, 
  Check, 
  X, 
  FileUp, 
  ExternalLink,
  ChevronRight,
  ChevronDown, 
  Eye,         
  EyeOff,      
  Edit,        
  Info,
  Calendar,
  Layers,
  ShieldCheck,
  ChevronLeft,
  Users,
  UserCheck,
  Inbox,
  Award,
  TrendingUp,
  HelpCircle,
  Plus,
  Trash2,
  Sliders,
  Sparkles,
  Database
} from 'lucide-react';

const INITIAL_COURSES = [
  { id: 'TH-01', name: 'ภาษาไทยเพื่อการสื่อสาร ระดับ 1', lang: 'ไทย', level: 1, price: 1500, prerequisite: null },
  { id: 'TH-02', name: 'ภาษาไทยเพื่อการสื่อสาร ระดับ 2', lang: 'ไทย', level: 2, price: 1800, prerequisite: 'TH-01' },
  { id: 'EN-01', name: 'English Communication Level 1', lang: 'อังกฤษ', level: 1, price: 2000, prerequisite: null },
  { id: 'EN-02', name: 'English Communication Level 2', lang: 'อังกฤษ', level: 2, price: 2500, prerequisite: 'EN-01' },
  { id: 'LA-01', name: 'ภาษาลาวขั้นต้น ระดับ 1', lang: 'ลาว', level: 1, price: 1200, prerequisite: null },
  { id: 'LA-02', name: 'ภาษาลาวขั้นสูง ระดับ 2', lang: 'ลาว', level: 2, price: 1500, prerequisite: 'LA-01' },
];

const INITIAL_EXAMS = [
  { id: 'EX-TOEIC', type: 'TOEIC', name: 'TOEIC Official Test', price: 1800, date: '2026-08-15' },
  { id: 'EX-EPT', type: 'EPT', name: 'English Proficiency Test (EPT)', price: 800, date: '2026-08-20' }
];

const INITIAL_ADMINS = [
  { id: 'adm-01', name: 'ดร. อนันต์ ไกรแก้ว', email: 'anan.kr@cars.ac.th', role: 'Super Admin', active: true },
  { id: 'adm-02', name: 'รศ. ดร. ณภัทร สินธุ', email: 'napat.si@cars.ac.th', role: 'Translation Manager', active: true },
  { id: 'adm-03', name: 'คุณวิชุดา พรเลิศ', email: 'wichuda.po@cars.ac.th', role: 'Finance Officer', active: true }
];

const INITIAL_USERS = [
  { email: 'somchai.dev@gmail.com', name: 'สมชาย รักเรียน', phone: '081-234-5678', isVerified: true, regDate: '2026-07-01' },
  { email: 'somsri.academic@gmail.com', name: 'สมศรี มีความรู้', phone: '089-876-5432', isVerified: true, regDate: '2026-07-05' },
  { email: 'john.doe@gmail.com', name: 'John Doe', phone: '095-555-1234', isVerified: false, regDate: '2026-07-16' }
];

const INITIAL_EMAIL_TEMPLATES = {
  welcome: {
    subject: "ยินดีต้อนรับเข้าสู่ระบบพอร์ทัล CARS-HUSOC",
    body: "บัญชีของคุณได้รับการเปิดใช้งานอย่างเป็นทางการเรียบร้อยแล้ว ยินดีต้อนรับสู่ระบบพัฒนาและประเมินทักษะทางภาษา"
  },
  payment_approved: {
    subject: "แจ้งยืนยันการรับชำระเงินค่าบริการอย่างเป็นทางการ",
    body: "สลิปโอนเงินของคุณได้รับการยืนยันและอนุมัติสิทธิ์เรียบร้อยแล้ว ปัจจุบันสามารถเข้าใช้งานหลักสูตรหรือห้องสอบที่เลือกได้ทันที"
  },
  score_released: {
    subject: "ผลคะแนนสอบประเมินภาษาออกอย่างเป็นทางการแล้ว",
    body: "ผลคะแนนของคุณได้ถูกบันทึกและตรวจสอบเรียบร้อยแล้ว ปัจจุบันสามารถเข้าดูและดาวน์โหลด Digital Certificate ได้ในระบบพอร์ทัลส่วนตัว"
  }
};

export default function App() {
  const [currentUser, setCurrentUser] = useState({
    email: 'somchai.dev@gmail.com',
    name: 'somchai.dev@gmail.com',
    isVerified: true
  });

  const [currentRole, setCurrentRole] = useState('user'); // 'guest' | 'user' | 'admin'
  const [activeTab, setActiveTab] = useState('training'); // สำหรับหน้า User/Guest: 'training' | 'exam' | 'translation' | 'my-portfolio'
  const [adminTab, setAdminTab] = useState('stats'); // แอดมินแท็บ
  const [isDataMenuOpen, setIsDataMenuOpen] = useState(true);

  // ควบคุมซ่อนคอร์สและสอบ
  const [hiddenCourses, setHiddenCourses] = useState([]);
  const [hiddenExams, setHiddenExams] = useState([]);

  // แจ้งเตือน Toast
  const [toast, setToast] = useState(null);

  // --- ฐานข้อมูลหลักใน Memory State ---
  const [courses, setCourses] = useState(INITIAL_COURSES);
  const [exams, setExams] = useState(INITIAL_EXAMS);
  const [adminsList, setAdminsList] = useState(INITIAL_ADMINS);
  const [usersList, setUsersList] = useState(INITIAL_USERS);
  const [emailTemplates, setEmailTemplates] = useState(INITIAL_EMAIL_TEMPLATES);

  // สเตตัสแถบเลือกข้อมูลของแอดมิน
  const [selectedExamId, setSelectedExamId] = useState('EX-TOEIC');
  const [selectedCourseId, setSelectedCourseId] = useState('TH-01');

  // ข้อมูลเทมเพลตใบรับรอง
  const [certTemplate, setCertTemplate] = useState({
    title: 'CERTIFICATE OF ACHIEVEMENT',
    subTitle: 'ขอมอบใบรับรองผลฉบับนี้เพื่อเกียรติประวัติวิชาการให้แก่',
    signatory1: 'ดร. อนันต์ ไกรแก้ว',
    signatory1Sub: 'ผู้อำนวยการศูนย์วิจัยและบริการวิชาการ (CARS)',
    borderColor: '#4f46e5',
    backgroundColor: '#ffffff'
  });

  const [enrollments, setEnrollments] = useState([
    { id: 'enr-1', userId: 'somchai.dev@gmail.com', courseId: 'TH-01', status: 'Passed', paymentStatus: 'Approved' },
    { id: 'enr-2', userId: 'somchai.dev@gmail.com', courseId: 'EN-01', status: 'Studying', paymentStatus: 'Approved' },
    { id: 'enr-3', userId: 'somsri.academic@gmail.com', courseId: 'TH-01', status: 'Studying', paymentStatus: 'Approved' },
    { id: 'enr-4', userId: 'john.doe@gmail.com', courseId: 'EN-01', status: 'Passed', paymentStatus: 'Approved' },
  ]);

  const [examRegistrations, setExamRegistrations] = useState([
    { id: 'reg-1', userId: 'somchai.dev@gmail.com', examId: 'EX-TOEIC', paymentStatus: 'Approved', score: null, certIssued: false },
    { id: 'reg-2', userId: 'somsri.academic@gmail.com', examId: 'EX-TOEIC', paymentStatus: 'Approved', score: 850, certIssued: true },
    { id: 'reg-3', userId: 'john.doe@gmail.com', examId: 'EX-EPT', paymentStatus: 'Approved', score: 72, certIssued: true },
  ]);

  const [translationRequests, setTranslationRequests] = useState([
    { 
      id: 'tx-1001', 
      userId: 'somchai.dev@gmail.com', 
      fileName: 'resume_somchai.pdf', 
      sourceLang: 'ไทย', 
      targetLang: 'อังกฤษ', 
      status: 'Quote_Sent',
      estimatedPrice: 450, 
      deliveryDate: '2026-07-25',
      paymentStatus: 'Pending',
      translatedFile: null
    }
  ]);

  const [payments, setPayments] = useState([
    { id: 'pay-01', userId: 'somchai.dev@gmail.com', type: 'Course', refId: 'TH-01', amount: 1500, slip: 'slip_demo1.jpg', status: 'Approved' },
    { id: 'pay-02', userId: 'somchai.dev@gmail.com', type: 'Course', refId: 'EN-01', amount: 2000, slip: 'slip_demo2.jpg', status: 'Approved' },
    { id: 'pay-03', userId: 'somchai.dev@gmail.com', type: 'Translation', refId: 'tx-1001', amount: 450, slip: null, status: 'Pending' }
  ]);

  const [emails, setEmails] = useState([
    { id: 1, to: 'somchai.dev@gmail.com', subject: 'ยินดีต้อนรับเข้าสู่ระบบพอร์ทัล CARS-HUSOC', body: 'บัญชีของคุณเข้าใช้งานอย่างเป็นทางการผ่านสิทธิ์ระดับพอร์ทัลความร่วมมือและบริการวิชาการของสถาบันเรียบร้อยแล้ว', date: '2026-07-16' },
  ]);

  const [paymentModalData, setPaymentModalData] = useState(null); 
  const [uploadedSlip, setUploadedSlip] = useState(null);
  const [translationFile, setTranslationFile] = useState('');
  const [sourceLang, setSourceLang] = useState('ไทย');
  const [targetLang, setTargetLang] = useState('อังกฤษ');

  // ข้อมูลนำเข้าของ Excel
  const [csvText, setCsvText] = useState("somchai.dev@gmail.com,785\nsomsri.academic@gmail.com,920");
  const [viewingCertificate, setViewingCertificate] = useState(null); 

  // ข้อมูลฟอร์มแอดมิน / ยูสเซอร์
  const [newAdminName, setNewAdminName] = useState('');
  const [newAdminEmail, setNewAdminEmail] = useState('');
  const [newAdminRole, setNewAdminRole] = useState('Staff');

  const [newUserEmail, setNewUserEmail] = useState('');
  const [newUserName, setNewUserName] = useState('');
  const [newUserPhone, setNewUserPhone] = useState('');

  const [courseForm, setCourseForm] = useState({ id: '', name: '', lang: 'อังกฤษ', level: 1, price: 1000, prerequisite: '' });
  const [examForm, setExamForm] = useState({ id: '', type: 'TOEIC', name: '', price: 1500, date: '' });

  const showToast = (msg) => {
    setToast(msg);
    setTimeout(() => setToast(null), 3500);
  };

  useEffect(() => {
    if (currentRole === 'guest' && (activeTab === 'translation' || activeTab === 'my-portfolio')) {
      setActiveTab('training');
    }
  }, [currentRole, activeTab]);

  const triggerEmail = (subject, body, targetUserEmail = currentUser.email) => {
    const newEmail = {
      id: Date.now(),
      to: targetUserEmail,
      subject: subject,
      body: body,
      date: new Date().toISOString().split('T')[0]
    };
    setEmails(prev => [newEmail, ...prev]);
  };

  const getEnrollmentStatus = (courseId) => {
    const enroll = enrollments.find(e => e.courseId === courseId && e.userId === currentUser.email);
    return enroll ? enroll.status : null; 
  };

  const getCoursePaymentStatus = (courseId) => {
    const enroll = enrollments.find(e => e.courseId === courseId && e.userId === currentUser.email);
    return enroll ? enroll.paymentStatus : null; 
  };

  const checkCourseEligibility = (course) => {
    const currentStatus = getEnrollmentStatus(course.id);
    if (currentStatus === 'Passed') {
      return { eligible: false, reason: 'ผ่านการอบรมหลักสูตรนี้เรียบร้อยแล้ว' };
    }
    if (currentStatus === 'Studying' || currentStatus === 'Pending_Payment') {
      return { eligible: false, reason: 'อยู่ระหว่างการอบรมหรือรอตรวจสอบสลิป' };
    }
    if (course.prerequisite) {
      const prereqStatus = getEnrollmentStatus(course.prerequisite);
      if (prereqStatus !== 'Passed') {
        const prereqCourse = courses.find(c => c.id === course.prerequisite);
        return { 
          eligible: false, 
          reason: `ต้องสอบผ่านวิชา "${prereqCourse ? prereqCourse.name : course.prerequisite}" ก่อน` 
        };
      }
    }
    return { eligible: true, reason: 'พร้อมเปิดให้สมัครลงเรียน' };
  };

  const handleCourseEnroll = (course) => {
    if (currentRole === 'guest') return;
    const check = checkCourseEligibility(course);
    if (!check.eligible) return;

    const newEnrollment = {
      id: 'enr-' + Date.now(),
      userId: currentUser.email,
      courseId: course.id,
      status: 'Pending_Payment',
      paymentStatus: 'Pending'
    };

    setEnrollments(prev => [...prev, newEnrollment]);
    
    setPaymentModalData({
      type: 'Course',
      refId: course.id,
      title: course.name,
      amount: course.price,
      enrollmentId: newEnrollment.id
    });

    triggerEmail(
      `ยืนยันการรับสมัครหลักสูตร: ${course.name}`,
      `คุณได้ทำรายการจองสิทธิ์เรียบร้อยแล้ว กรุณาดำเนินการแนบสลิปชำระเงินจำนวน ${course.price} บาท เพื่อระบบจะอนุมัติสถานะการเข้าอบรมวิชาถัดไป`
    );
  };

  const handleExamRegister = (exam) => {
    if (currentRole === 'guest') return;
    const isRegistered = examRegistrations.find(r => r.examId === exam.id && r.userId === currentUser.email);
    if (isRegistered) {
      showToast("ท่านได้ส่งใบสมัครรอบทดสอบภาษานี้ไปแล้ว");
      return;
    }

    const newReg = {
      id: 'reg-' + Date.now(),
      userId: currentUser.email,
      examId: exam.id,
      paymentStatus: 'Pending',
      score: null,
      certIssued: false
    };

    setExamRegistrations(prev => [...prev, newReg]);

    setPaymentModalData({
      type: 'Exam',
      refId: exam.id,
      title: exam.name,
      amount: exam.price,
      registrationId: newReg.id
    });

    triggerEmail(
      `ได้รับข้อมูลใบสมัครการทดสอบ ${exam.type} แล้ว`,
      `โปรดทำการโอนเงินเพื่อรักษาสิทธิ์ที่นั่งสอบและอัปโหลดสลิปจำนวน ${exam.price} บาท ให้ระบบตรวจยืนยันสิทธิ์`
    );
  };

  const handleTranslationSubmit = (e) => {
    e.preventDefault();
    if (!translationFile) {
      showToast("กรุณากรอกชื่อเอกสารที่ต้องการแปล");
      return;
    }

    const newReq = {
      id: 'tx-' + (1000 + translationRequests.length + 1),
      userId: currentUser.email,
      fileName: translationFile,
      sourceLang,
      targetLang,
      status: 'Submitted',
      estimatedPrice: null,
      deliveryDate: null,
      paymentStatus: 'Pending',
      translatedFile: null
    };

    setTranslationRequests(prev => [newReq, ...prev]);
    setTranslationFile('');

    triggerEmail(
      'ยื่นเอกสารแปลเข้าสู่ระบบแล้ว',
      `เอกสารของท่านหัวข้อ "${newReq.fileName}" จากภาษา${sourceLang} เป็นภาษา${targetLang} ได้รับการยื่นเพื่อประเมินราคาเรียบร้อยแล้ว`
    );
  };

  const handleUploadSlip = () => {
    if (!uploadedSlip) {
      showToast("กรุณาระบุหรือจำลองไฟล์สลิป");
      return;
    }

    const newPayment = {
      id: 'pay-' + Date.now(),
      userId: currentUser.email,
      type: paymentModalData.type,
      refId: paymentModalData.refId,
      amount: paymentModalData.amount,
      slip: uploadedSlip,
      status: 'Pending'
    };

    setPayments(prev => [newPayment, ...prev]);

    if (paymentModalData.type === 'Course') {
      setEnrollments(prev => prev.map(e => 
        e.courseId === paymentModalData.refId && e.userId === currentUser.email
          ? { ...e, paymentStatus: 'Pending' }
          : e
      ));
    } else if (paymentModalData.type === 'Exam') {
      setExamRegistrations(prev => prev.map(r => 
        r.examId === paymentModalData.refId && r.userId === currentUser.email
          ? { ...r, paymentStatus: 'Pending' }
          : r
      ));
    } else if (paymentModalData.type === 'Translation') {
      setTranslationRequests(prev => prev.map(t => 
        t.id === paymentModalData.refId && t.userId === currentUser.email
          ? { ...t, paymentStatus: 'Pending' }
          : t
      ));
    }

    triggerEmail(
      `ได้รับข้อมูลใบเสร็จสลิปจำนวน ${paymentModalData.amount} บาท แล้ว`,
      `เอกสารนำส่งสลิปโอนเงินของคุณได้บันทึกเข้าระบบเพื่อรอให้เจ้าหน้าที่อนุมัติต่อไป`
    );

    setPaymentModalData(null);
    setUploadedSlip(null);
    showToast("ส่งหลักฐานสลิปและโอนชำระเรียบร้อย!");
  };

  const adminApprovePayment = (pay) => {
    setPayments(prev => prev.map(p => p.id === pay.id ? { ...p, status: 'Approved' } : p));

    if (pay.type === 'Course') {
      setEnrollments(prev => prev.map(e => 
        e.courseId === pay.refId && e.userId === pay.userId
          ? { ...e, paymentStatus: 'Approved', status: 'Studying' }
          : e
      ));
      const c = courses.find(item => item.id === pay.refId);
      triggerEmail(
        emailTemplates.payment_approved.subject,
        `หลักสูตร "${c?.name}": ${emailTemplates.payment_approved.body}`,
        pay.userId
      );
    } else if (pay.type === 'Exam') {
      setExamRegistrations(prev => prev.map(r => 
        r.examId === pay.refId && r.userId === pay.userId
          ? { ...r, paymentStatus: 'Approved' }
          : r
      ));
      const ex = exams.find(item => item.id === pay.refId);
      triggerEmail(
        emailTemplates.payment_approved.subject,
        `การสมัครสอบประเมินรอบ "${ex?.name}": ${emailTemplates.payment_approved.body}`,
        pay.userId
      );
    } else if (pay.type === 'Translation') {
      setTranslationRequests(prev => prev.map(t => 
        t.id === pay.refId && t.userId === pay.userId
          ? { ...t, paymentStatus: 'Approved', status: 'Translating' }
          : t
      ));
      triggerEmail(
        emailTemplates.payment_approved.subject,
        `งานแปลเอกสารยืนยันสิทธิ์: ${emailTemplates.payment_approved.body}`,
        pay.userId
      );
    }
    showToast("อนุมัติรับเงินและการโอนสำเร็จ!");
  };

  const handleExamScoreImportExcel = () => {
    const lines = csvText.split('\n');
    let importedCount = 0;

    lines.forEach(line => {
      const parts = line.split(',');
      if (parts.length >= 2) {
        const emailInput = parts[0].trim();
        const scoreInput = parseInt(parts[1].trim(), 10);

        setExamRegistrations(prev => {
          const exists = prev.some(r => r.userId === emailInput && r.examId === selectedExamId);
          if (exists) {
            return prev.map(r => 
              r.userId === emailInput && r.examId === selectedExamId
                ? { ...r, score: scoreInput, certIssued: true, paymentStatus: 'Approved' }
                : r
            );
          } else {
            return [...prev, {
              id: 'reg-imported-' + Date.now() + '-' + importedCount,
              userId: emailInput,
              examId: selectedExamId,
              paymentStatus: 'Approved',
              score: scoreInput,
              certIssued: true
            }];
          }
        });

        triggerEmail(
          emailTemplates.score_released.subject,
          `${emailTemplates.score_released.body} - คะแนนสอบรอบรหัส ${selectedExamId} ที่ได้รับคือ: ${scoreInput} คะแนน`,
          emailInput
        );
        importedCount++;
      }
    });

    showToast(`ระบบบันทึกนำเข้าข้อมูลคะแนนสำเร็จ จำนวน ${importedCount} รายชื่อ`);
  };

  const updateCourseGrade = (enrollmentId, newStatus) => {
    setEnrollments(prev => prev.map(e => {
      if (e.id === enrollmentId) {
        const course = courses.find(c => c.id === e.courseId);
        if (newStatus === 'Passed') {
          triggerEmail(
            `ยินดีด้วย! คุณผ่านเกณฑ์การฝึกอบรมคอร์ส: ${course?.name} แล้ว`,
            `คุณผ่านหลักสูตรพัฒนาภาษาเรียบร้อยและมีสิทธิ์ได้รับใบรับรอง พร้อมเปิดระบบให้ลงทะเบียนเรียนระดับถัดไปแล้ว`,
            e.userId
          );
        }
        return { ...e, status: newStatus };
      }
      return e;
    }));
    showToast("ปรับปรุงสถานะการตัดเกรดเรียบร้อย!");
  };

  const submitTranslationQuote = (reqId, price, days) => {
    const delivery = new Date();
    delivery.setDate(delivery.getDate() + parseInt(days, 10));
    const deliveryStr = delivery.toISOString().split('T')[0];

    setTranslationRequests(prev => prev.map(t => 
      t.id === reqId 
        ? { ...t, status: 'Quote_Sent', estimatedPrice: price, deliveryDate: deliveryStr }
        : t
    ));

    const targetReq = translationRequests.find(t => t.id === reqId);
    triggerEmail(
      `ใบประเมินค่าใช้จ่ายงานแปลรหัส #${reqId} ส่งเรียบร้อยแล้ว`,
      `ประเมินอัตราค่าบริการ: ${price} บาท คาดการณ์แล้วเสร็จจัดส่งคืน: ${deliveryStr} กรุณาทำการยอมรับและอัปโหลดหลักฐานแจ้งเพื่อยืนยัน`,
      targetReq?.userId
    );
    showToast("จัดส่งใบประเมินค่าแปลแล้ว!");
  };

  const deliverTranslation = (reqId, mockLink) => {
    setTranslationRequests(prev => prev.map(t => 
      t.id === reqId 
        ? { ...t, status: 'Completed', translatedFile: mockLink || 'translated_document_final.pdf' }
        : t
    ));

    const targetReq = translationRequests.find(t => t.id === reqId);
    triggerEmail(
      `ส่งมอบงานแปลไฟล์จัดส่งกลับถึงท่านแล้ว!`,
      `ใบงานส่งมอบคำขอแปลเลขรหัส #${reqId} ได้รับการตรวจสอบและแปลแล้วเสร็จอย่างเป็นทางการเรียบร้อย สามารถดาวน์โหลดผลงานได้ในเมนูศูนย์แปล`,
      targetReq?.userId
    );
    showToast("ส่งมอบงานแปลสำเร็จ!");
  };

  const handleAddAdmin = (e) => {
    e.preventDefault();
    if (!newAdminName || !newAdminEmail) return;
    const newAdm = {
      id: 'adm-' + Date.now(),
      name: newAdminName,
      email: newAdminEmail,
      role: newAdminRole,
      active: true
    };
    setAdminsList(prev => [...prev, newAdm]);
    setNewAdminName('');
    setNewAdminEmail('');
    showToast('เพิ่มเจ้าหน้าที่และมอบหมายสิทธิ์สำเร็จ!');
  };

  const handleAddUser = (e) => {
    e.preventDefault();
    if (!newUserEmail || !newUserName) return;
    const newUsr = {
      email: newUserEmail,
      name: newUserName,
      phone: newUserPhone || '-',
      isVerified: true,
      regDate: new Date().toISOString().split('T')[0]
    };
    setUsersList(prev => [...prev, newUsr]);
    setNewUserEmail('');
    setNewUserName('');
    setNewUserPhone('');
    showToast('บันทึกเพิ่มบัญชีผู้ลงทะเบียนรายใหม่สำเร็จ!');
  };

  const handleToggleCourseVisibility = (courseId) => {
    setHiddenCourses(prev => 
      prev.includes(courseId) 
        ? prev.filter(id => id !== courseId) 
        : [...prev, courseId]
    );
    showToast(`อัปเดตสิทธิ์การแสดงผลของหลักสูตร ${courseId} แล้ว`);
  };

  const handleToggleExamVisibility = (examId) => {
    setHiddenExams(prev => 
      prev.includes(examId) 
        ? prev.filter(id => id !== examId) 
        : [...prev, examId]
    );
    showToast(`อัปเดตสิทธิ์การแสดงผลของรอบจัดสอบ ${examId} แล้ว`);
  };

  const handleEditCourse = (course) => {
    setCourseForm({
      id: course.id,
      name: course.name,
      lang: course.lang,
      level: course.level,
      price: course.price,
      prerequisite: course.prerequisite || ''
    });
    showToast(`โหลดข้อมูลหลักสูตร ${course.id} เข้าฟอร์มแล้ว กรุณาแก้ไขด้านบน`);
  };

  const handleEditExam = (exam) => {
    setExamForm({
      id: exam.id,
      type: exam.type,
      name: exam.name,
      price: exam.price,
      date: exam.date
    });
    showToast(`โหลดข้อมูลรอบจัดสอบ ${exam.id} เข้าฟอร์มแล้ว กรุณาแก้ไขด้านบน`);
  };

  const handleAddCourse = (e) => {
    e.preventDefault();
    if (!courseForm.id || !courseForm.name) return;
    
    setCourses(prev => {
      const exists = prev.some(c => c.id === courseForm.id);
      if (exists) {
        return prev.map(c => c.id === courseForm.id ? {
          ...courseForm,
          level: parseInt(courseForm.level, 10),
          price: parseInt(courseForm.price, 10),
          prerequisite: courseForm.prerequisite || null
        } : c);
      } else {
        return [...prev, {
          ...courseForm,
          level: parseInt(courseForm.level, 10),
          price: parseInt(courseForm.price, 10),
          prerequisite: courseForm.prerequisite || null
        }];
      }
    });

    setCourseForm({ id: '', name: '', lang: 'อังกฤษ', level: 1, price: 1000, prerequisite: '' });
    showToast('บันทึกและปรับปรุงตารางหลักสูตรเรียบร้อย!');
  };

  const handleAddExam = (e) => {
    e.preventDefault();
    if (!examForm.id || !examForm.name) return;

    setExams(prev => {
      const exists = prev.some(ex => ex.id === examForm.id);
      if (exists) {
        return prev.map(ex => ex.id === examForm.id ? {
          ...examForm,
          price: parseInt(examForm.price, 10)
        } : ex);
      } else {
        return [...prev, {
          ...examForm,
          price: parseInt(examForm.price, 10)
        }];
      }
    });

    setExamForm({ id: '', type: 'TOEIC', name: '', price: 1500, date: '' });
    showToast('ปรับปรุงข้อมูลการเปิดสอบประเมินชุดใหม่สำเร็จ!');
  };

  return (
    <div className="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans relative">
      
      {/* TOAST SYSTEM */}
      {toast && (
        <div className="fixed bottom-5 right-5 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl z-50 flex items-center gap-2 border border-indigo-500 animate-in fade-in slide-in-from-bottom-5 duration-300">
          <Sparkles className="h-4 w-4 text-amber-400" />
          <span className="text-xs font-bold">{toast}</span>
        </div>
      )}

      {/* TOP BRAND HEADER & ROLE SWITCHER */}
      <header className="bg-slate-900 text-white sticky top-0 z-40 border-b border-slate-800 px-6 py-4 shadow-md">
        <div className="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-4">
          <div className="flex items-center gap-4">
            <div className="bg-white p-1.5 rounded-xl shadow-inner">
              <img 
                src="logo (1).png" 
                alt="CARS HUSOC Logo" 
                className="h-12 w-12 object-contain"
                onError={(e) => {
                  e.target.onerror = null;
                  e.target.src = "https://placehold.co/48x48/4f46e5/ffffff?text=CARS";
                }}
              />
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h1 className="text-xl font-extrabold tracking-tight">CARS - HUSOC</h1>
                <span className="bg-indigo-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">PORTAL</span>
              </div>
              <p className="text-[10px] text-slate-400 font-medium tracking-wide leading-tight">
                ศูนย์บริการวิชาการและวิจัย คณะมนุษยศาสตร์และสังคมศาสตร์
              </p>
            </div>
          </div>
          
          <div className="flex items-center gap-4 flex-wrap">
            <div className="flex items-center gap-1 bg-slate-800 p-1.5 rounded-xl border border-slate-750 shadow-inner">
              <button 
                onClick={() => setCurrentRole('guest')}
                className={`py-1.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 ${
                  currentRole === 'guest' 
                    ? 'bg-amber-500 text-slate-900 shadow-sm' 
                    : 'text-slate-300 hover:text-white'
                }`}
              >
                <Lock className="h-3.5 w-3.5" />
                ยังไม่ได้ login
              </button>
              <button 
                onClick={() => setCurrentRole('user')}
                className={`py-1.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 ${
                  currentRole === 'user' 
                    ? 'bg-indigo-600 text-white shadow-sm' 
                    : 'text-slate-300 hover:text-white'
                }`}
              >
                <UserCheck className="h-3.5 w-3.5" />
                ผู้ใช้ (User)
              </button>
              <button 
                onClick={() => {
                  setCurrentRole('admin');
                  setAdminTab('stats');
                }}
                className={`py-1.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 ${
                  currentRole === 'admin' 
                    ? 'bg-indigo-600 text-white shadow-sm' 
                    : 'text-slate-300 hover:text-white'
                }`}
              >
                <ShieldCheck className="h-3.5 w-3.5" />
                แอดมิน (Admin)
              </button>
            </div>

            <span className="text-xs text-slate-400 bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700 font-mono">
              v2.6.5
            </span>
          </div>
        </div>
      </header>

      {/* MAIN CONTENT AREA - Sidebar Left + Content Right */}
      <div className="max-w-7xl mx-auto px-4 py-8 flex-grow w-full grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {/* SIDEBAR (LEFT COLUMN) */}
        <aside className="lg:col-span-3 space-y-6 flex flex-col justify-start">
          
          <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            
            <div className="p-4 space-y-1">
              <span className="block text-[10px] uppercase font-bold text-slate-400 tracking-widest px-2 mb-2">
                {currentRole === 'admin' ? 'เมนูระบบผู้ดูแลระบบ' : 'เมนูบริการวิชาการ'}
              </span>

              {/* USER & GUEST NAVIGATION */}
              {currentRole !== 'admin' && (
                <nav className="space-y-1.5">
                  <button
                    onClick={() => setActiveTab('training')}
                    className={`w-full flex items-center gap-3 px-3 py-3 rounded-xl text-xs font-bold text-left transition-all ${
                      activeTab === 'training'
                        ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                        : 'text-slate-600 hover:bg-slate-50'
                    }`}
                  >
                    <BookOpen className="h-4 w-4 text-indigo-600" />
                    <span>ศูนย์อบรมภาษา (อบรม)</span>
                  </button>

                  <button
                    onClick={() => setActiveTab('exam')}
                    className={`w-full flex items-center gap-3 px-3 py-3 rounded-xl text-xs font-bold text-left transition-all ${
                      activeTab === 'exam'
                        ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                        : 'text-slate-600 hover:bg-slate-50'
                    }`}
                  >
                    <GraduationCap className="h-4 w-4 text-indigo-600" />
                    <span>ศูนย์สอบภาษาอังกฤษ</span>
                  </button>

                  {currentRole === 'user' && (
                    <>
                      <button
                        onClick={() => setActiveTab('translation')}
                        className={`w-full flex items-center gap-3 px-3 py-3 rounded-xl text-xs font-bold text-left transition-all ${
                          activeTab === 'translation'
                            ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                            : 'text-slate-600 hover:bg-slate-50'
                        }`}
                      >
                        <FileText className="h-4 w-4 text-indigo-600" />
                        <span>ศูนย์แปลเอกสาร</span>
                      </button>

                      <button
                        onClick={() => setActiveTab('my-portfolio')}
                        className={`w-full flex items-center gap-3 px-3 py-3 rounded-xl text-xs font-bold text-left transition-all ${
                          activeTab === 'my-portfolio'
                            ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                            : 'text-slate-600 hover:bg-slate-50'
                        }`}
                      >
                        <CheckCircle className="h-4 w-4 text-emerald-600" />
                        <span>ประวัติผลงาน & ใบรับรอง</span>
                      </button>
                    </>
                  )}
                </nav>
              )}

              {/* ADMIN NAVIGATION WITH COLLAPSIBLE DATA MANAGEMENT */}
              {currentRole === 'admin' && (
                <nav className="space-y-1">
                  <button
                    onClick={() => setAdminTab('stats')}
                    className={`w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all ${
                      adminTab === 'stats'
                        ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                        : 'text-slate-600 hover:bg-slate-50'
                    }`}
                  >
                    <TrendingUp className="h-4 w-4 text-indigo-600" />
                    <span>วิเคราะห์ข้อมูล & สถิติ (3.6)</span>
                  </button>

                  <button
                    onClick={() => setAdminTab('slips')}
                    className={`w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all ${
                      adminTab === 'slips'
                        ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                        : 'text-slate-600 hover:bg-slate-50'
                    }`}
                  >
                    <div className="flex items-center gap-2.5">
                      <DollarSign className="h-4 w-4 text-indigo-600" />
                      <span>ตรวจสลิปค่าบริการ</span>
                    </div>
                    {payments.filter(p => p.status === 'Pending').length > 0 && (
                      <span className="bg-amber-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full">
                        {payments.filter(p => p.status === 'Pending').length}
                      </span>
                    )}
                  </button>

                  <button
                    onClick={() => setAdminTab('admins')}
                    className={`w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all ${
                      adminTab === 'admins'
                        ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                        : 'text-slate-600 hover:bg-slate-50'
                    }`}
                  >
                    <UserCheck className="h-4 w-4 text-indigo-600" />
                    <span>จัดการเจ้าหน้าที่แอดมิน (3.1)</span>
                  </button>

                  <button
                    onClick={() => setAdminTab('users')}
                    className={`w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all ${
                      adminTab === 'users'
                        ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                        : 'text-slate-600 hover:bg-slate-50'
                    }`}
                  >
                    <Users className="h-4 w-4 text-indigo-600" />
                    <span>จัดการผู้ลงทะเบียน (3.2)</span>
                  </button>

                  <button
                    onClick={() => setAdminTab('courses-manage')}
                    className={`w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all ${
                      adminTab === 'courses-manage'
                        ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                        : 'text-slate-600 hover:bg-slate-50'
                    }`}
                  >
                    <Sliders className="h-4 w-4 text-indigo-600" />
                    <span>จัดการหลักสูตรและสอบ (3.3)</span>
                  </button>

                  {/* COLLAPSIBLE DATA MANAGEMENT WRAPPER */}
                  <div className="pt-2 pb-1 border-t border-slate-100 my-2">
                    <button
                      onClick={() => setIsDataMenuOpen(!isDataMenuOpen)}
                      className="w-full text-left text-[9px] uppercase font-bold text-slate-400 tracking-wider px-3 mb-1.5 flex items-center justify-between hover:text-slate-600 transition-colors"
                    >
                      <span className="flex items-center gap-1">
                        <Database className="h-3 w-3 text-indigo-500" />
                        จัดการข้อมูล (Data Management)
                      </span>
                      {isDataMenuOpen ? (
                        <ChevronDown className="h-3 w-3 text-slate-400" />
                      ) : (
                        <ChevronRight className="h-3 w-3 text-slate-400" />
                      )}
                    </button>
                    
                    {isDataMenuOpen && (
                      <div className="space-y-1 px-1 transition-all duration-200">
                        <button
                          onClick={() => setAdminTab('manage-exams')}
                          className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-left transition-all ${
                            adminTab === 'manage-exams'
                              ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                              : 'text-slate-600 hover:bg-slate-50'
                          }`}
                        >
                          <GraduationCap className="h-3.5 w-3.5 text-indigo-600" />
                          <span>จัดการข้อมูลการสอบ</span>
                        </button>

                        <button
                          onClick={() => setAdminTab('manage-courses')}
                          className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-left transition-all ${
                            adminTab === 'manage-courses'
                              ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                              : 'text-slate-600 hover:bg-slate-50'
                          }`}
                        >
                          <BookOpen className="h-3.5 w-3.5 text-indigo-600" />
                          <span>จัดการข้อมูลอบรม</span>
                        </button>

                        <button
                          onClick={() => setAdminTab('translation-quotes')}
                          className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-left transition-all ${
                            adminTab === 'translation-quotes'
                              ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                              : 'text-slate-600 hover:bg-slate-50'
                          }`}
                        >
                          <FileText className="h-3.5 w-3.5 text-indigo-600" />
                          <span>ประเมินราคางานแปล</span>
                        </button>
                      </div>
                    )}
                  </div>

                  <button
                    onClick={() => setAdminTab('email-templates')}
                    className={`w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all ${
                      adminTab === 'email-templates'
                        ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                        : 'text-slate-600 hover:bg-slate-50'
                    }`}
                  >
                    <Mail className="h-4 w-4 text-indigo-600" />
                    <span>จัดการข้อความอีเมล (3.4)</span>
                  </button>

                  <button
                    onClick={() => setAdminTab('cert-templates')}
                    className={`w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all ${
                      adminTab === 'cert-templates'
                        ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                        : 'text-slate-600 hover:bg-slate-50'
                    }`}
                  >
                    <Award className="h-4 w-4 text-indigo-600" />
                    <span>แบบฟอร์มใบประกาศ (3.5)</span>
                  </button>

                  <button
                    onClick={() => setAdminTab('manual')}
                    className={`w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all ${
                      adminTab === 'manual'
                        ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 font-extrabold'
                        : 'text-slate-600 hover:bg-slate-50'
                    }`}
                  >
                    <HelpCircle className="h-4 w-4 text-indigo-600" />
                    <span>คู่มือการใช้งานระบบ (3.7)</span>
                  </button>
                </nav>
              )}
            </div>

            {/* USER PROFILE PANEL */}
            <div className="p-4 border-t border-slate-100 bg-slate-50 mt-auto">
              {currentRole === 'guest' ? (
                <div className="flex items-center gap-3">
                  <div className="bg-slate-200 text-slate-500 h-9 w-9 rounded-full flex items-center justify-center font-extrabold text-sm border border-slate-300">
                    <Lock className="h-4 w-4" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-xs font-bold text-red-600 truncate flex items-center gap-1">
                      <span className="h-2 w-2 rounded-full bg-red-500 animate-pulse"></span>
                      ยังไม่ได้เข้าสู่ระบบ
                    </p>
                    <p className="text-[10px] text-slate-500 truncate">กรุณา Log in เพื่อใช้บริการเต็มรูปแบบ</p>
                  </div>
                </div>
              ) : (
                <div className="flex items-center gap-3">
                  <div className="bg-indigo-100 text-indigo-700 h-9 w-9 rounded-full flex items-center justify-center font-extrabold text-sm border border-indigo-200">
                    {currentUser.name.substring(0, 2).toUpperCase()}
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-xs font-bold text-slate-800 truncate">{currentUser.name}</p>
                    <p className="text-[10px] text-slate-500 truncate">{currentUser.email}</p>
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* LIVE LOGS FOR ADMIN UNDER SIDEBAR */}
          {currentRole === 'admin' && (
            <div className="bg-slate-900 text-white p-4 rounded-2xl shadow-lg border border-slate-850 animate-in fade-in duration-300">
              <div className="flex justify-between items-center border-b border-slate-800 pb-2 mb-2">
                <h3 className="font-bold text-xs flex items-center gap-1.5 text-indigo-400">
                  <Mail className="h-4 w-4" />
                  ประวัติอีเมลส่งออกจากระบบ
                </h3>
                <span className="bg-indigo-600 text-white text-[9px] px-2 py-0.5 rounded-full font-black">
                  {emails.length}
                </span>
              </div>
              <p className="text-[9px] text-slate-400 mb-3 leading-relaxed">
                จำลองความเคลื่อนไหวอีเมลส่งออกระบบแบบเรียลไทม์
              </p>
              <div className="space-y-2 max-h-[220px] overflow-y-auto pr-1 scrollbar-thin">
                {emails.map((mail) => (
                  <div key={mail.id} className="p-3 bg-slate-800/60 rounded-xl border border-slate-750 text-[10px]">
                    <p className="font-bold text-indigo-300 mb-0.5">{mail.subject}</p>
                    <p className="text-slate-300 mb-1.5 leading-relaxed">{mail.body}</p>
                    <div className="flex justify-between items-center text-[8px] text-slate-500 border-t border-slate-700/40 pt-1.5">
                      <span className="truncate max-w-[120px]">ถึง: {mail.to}</span>
                      <span>{mail.date}</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

        </aside>

        {/* MAIN CONTENT SPACE (MIDDLE/RIGHT COLUMN) */}
        <main className="lg:col-span-9 space-y-6">

          {/* =========================================
              VIEW: USER SEGMENT (USER & GUEST)
             ========================================= */}
          {currentRole !== 'admin' && (
            <div className="space-y-6">
              
              {/* TAB: Training Center */}
              {activeTab === 'training' && (
                <div className="space-y-6">
                  
                  <div className="bg-gradient-to-br from-indigo-900 to-slate-900 text-white p-6 rounded-2xl shadow-md relative overflow-hidden">
                    <div className="absolute right-0 bottom-0 opacity-10 transform translate-x-12 translate-y-6">
                      <BookOpen className="h-48 w-48" />
                    </div>
                    <div className="relative z-10 max-w-2xl">
                      <span className="bg-indigo-500 text-white text-[9px] tracking-widest uppercase font-black px-2 py-0.5 rounded">
                        Language Training Center
                      </span>
                      <h2 className="text-xl font-extrabold mt-2">หลักสูตรพัฒนาและอบรมภาษาศาสตร์</h2>
                      <p className="text-slate-300 text-xs mt-1 leading-relaxed">
                        **ระเบียบการเรียน:** นักศึกษาทุกคนจำเป็นต้องเรียนและสอบผ่านเกณฑ์ประเมินใน "หลักสูตรระดับขั้นที่ 1" ของวิชาภาษานั้น ๆ ก่อน จึงจะได้รับการอนุญาตให้เข้าสมัครเรียนในระดับ "ระดับขั้นที่ 2" ถัดไปได้ตามระบบ Prerequisite
                      </p>
                    </div>
                  </div>

                  <div className="space-y-4">
                    <h3 className="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                      <Layers className="h-4 w-4 text-indigo-600" />
                      หลักสูตรฝึกอบรมที่เปิดลงทะเบียน
                    </h3>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                      {courses.filter(c => !hiddenCourses.includes(c.id)).map(course => {
                        const status = currentRole === 'guest' ? null : getEnrollmentStatus(course.id);
                        const payment = currentRole === 'guest' ? null : getCoursePaymentStatus(course.id);
                        const { eligible, reason } = currentRole === 'guest' ? { eligible: false, reason: 'กรุณา Login เพื่อตรวจสอบสิทธิ์' } : checkCourseEligibility(course);

                        return (
                          <div 
                            key={course.id} 
                            className={`p-4 rounded-xl bg-white border transition-all flex flex-col justify-between ${
                              status === 'Passed' 
                                ? 'border-emerald-200 bg-emerald-50/10' 
                                : 'border-slate-200 shadow-xs hover:shadow-sm'
                            }`}
                          >
                            <div>
                              <div className="flex justify-between items-start mb-2">
                                <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${
                                  course.lang === 'ไทย' ? 'bg-indigo-50 text-indigo-700' :
                                  course.lang === 'อังกฤษ' ? 'bg-amber-50 text-amber-800' :
                                  'bg-slate-100 text-slate-700'
                                }`}>
                                  ภาษา{course.lang}
                                </span>
                                <span className="text-slate-400 text-[10px] font-mono">รหัส: {course.id}</span>
                              </div>

                              <h4 className="font-extrabold text-slate-800 text-sm leading-snug">{course.name}</h4>
                              <p className="text-[10px] text-slate-500 mt-1">ระดับขั้นวิชา: ระดับ {course.level}</p>

                              {course.prerequisite && (
                                <div className="mt-2.5 flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">
                                  <Lock className="h-3 w-3 text-slate-400" />
                                  <p className="text-[10px] text-slate-500">
                                    Prerequisite: ผ่าน {course.prerequisite} ก่อน
                                  </p>
                                </div>
                              )}
                            </div>

                            <div className="mt-4 pt-3 border-t border-slate-50">
                              {currentRole === 'guest' ? (
                                <div className="space-y-1 text-center bg-amber-50/70 p-2.5 rounded-xl border border-amber-150 text-[10px]">
                                  <p className="text-amber-800 font-extrabold flex items-center justify-center gap-1">
                                    <Lock className="h-3.5 w-3.5" />
                                    ยังไม่ได้เข้าสู่ระบบ
                                  </p>
                                  <p className="text-slate-500">กรุณา Login เพื่อสมัครและดูค่าบำรุง</p>
                                </div>
                              ) : (
                                <div className="flex items-center justify-between">
                                  <div>
                                    <p className="text-[10px] text-slate-400">ค่าบำรุงวิชาเรียน</p>
                                    <p className="text-md font-black text-indigo-600">{course.price.toLocaleString()} ฿</p>
                                  </div>

                                  <div>
                                    {status === 'Passed' ? (
                                      <span className="flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-lg border border-emerald-100">
                                        <Check className="h-3.5 w-3.5" /> ผ่านหลักสูตร
                                      </span>
                                    ) : status === 'Studying' ? (
                                      <span className="bg-indigo-50 text-indigo-700 text-[10px] font-bold px-2 py-1 rounded-lg border border-indigo-100 animate-pulse">
                                        อยู่ระหว่างเข้าเรียน
                                      </span>
                                    ) : status === 'Pending_Payment' && payment === 'Pending' ? (
                                      <button
                                        onClick={() => setPaymentModalData({
                                          type: 'Course',
                                          refId: course.id,
                                          title: course.name,
                                          amount: course.price
                                        })}
                                        className="flex items-center gap-1 bg-amber-50 text-amber-700 text-[10px] font-bold px-2 py-1 rounded-lg border border-amber-200 hover:bg-amber-100 transition-colors"
                                      >
                                        <Clock className="h-3 w-3 animate-pulse" /> อัปโหลดสลิป
                                      </button>
                                    ) : !eligible ? (
                                      <span 
                                        title={reason}
                                        className="flex items-center gap-1 bg-slate-100 text-slate-400 text-[10px] font-bold px-2.5 py-1.5 rounded-lg cursor-not-allowed"
                                      >
                                        <Lock className="h-3.5 w-3.5" /> ล็อกวิชาเรียน
                                      </span>
                                    ) : (
                                      <button
                                        onClick={() => handleCourseEnroll(course)}
                                        className="flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg transition-all shadow-xs"
                                      >
                                        สมัครเรียน <ArrowRight className="h-3 w-3" />
                                      </button>
                                    )}
                                  </div>
                                </div>
                              )}
                            </div>
                            
                            {currentRole !== 'guest' && !eligible && status !== 'Passed' && (
                              <p className="text-[9px] text-red-500 mt-2 text-right italic font-medium">
                                * {reason}
                              </p>
                            )}
                          </div>
                        );
                      })}
                    </div>
                  </div>
                </div>
              )}

              {/* TAB: Exam Center */}
              {activeTab === 'exam' && (
                <div className="space-y-6">
                  <div className="bg-gradient-to-br from-slate-900 to-indigo-955 text-white p-6 rounded-2xl shadow-md">
                    <h2 className="text-xl font-bold">ศูนย์สอบวัดผลทักษะภาษาอังกฤษวิชาการ</h2>
                    <p className="text-slate-300 text-xs mt-1 leading-relaxed">
                      จัดการทดสอบมาตรฐานสากลและภายในเพื่อสนับสนุนการเข้าเรียนและการทำงาน สามารถดูคะแนนผ่านระบบได้ทันทีเมื่อแอดมินนำเข้าคะแนน
                    </p>
                  </div>

                  <div className="space-y-4">
                    <h3 className="text-sm font-bold text-slate-900">รอบจัดการประเมินสอบวัดระดับที่เปิดรับสมัคร</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      {exams.filter(e => !hiddenExams.includes(e.id)).map(exam => {
                        const reg = currentRole === 'guest' ? null : examRegistrations.find(r => r.examId === exam.id && r.userId === currentUser.email);

                        return (
                          <div key={exam.id} className="p-4 bg-white rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                            <div>
                              <div className="flex justify-between items-center mb-2">
                                <span className="bg-indigo-100 text-indigo-700 text-[10px] font-extrabold px-2 py-0.5 rounded-lg">
                                  {exam.type}
                                </span>
                                <div className="flex items-center gap-1 text-[10px] text-slate-400">
                                  <Calendar className="h-3 w-3" />
                                  {exam.date}
                                </div>
                              </div>
                              <h4 className="font-extrabold text-slate-800 text-sm">{exam.name}</h4>
                              <p className="text-[10px] text-slate-500 mt-1">รหัสกลุ่มจัดรอบสอบ: {exam.id}</p>
                            </div>

                            <div className="mt-4 pt-3 border-t border-slate-50">
                              {currentRole === 'guest' ? (
                                <div className="space-y-1 text-center bg-amber-50/75 p-2.5 rounded-xl border border-amber-150 text-[10px]">
                                  <p className="text-amber-800 font-extrabold flex items-center justify-center gap-1">
                                    <Lock className="h-3.5 w-3.5" />
                                    ยังไม่ได้เข้าสู่ระบบ
                                  </p>
                                  <p className="text-slate-500">กรุณา Login เพื่อเข้าสอบและดูอัตราค่าธรรมเนียม</p>
                                </div>
                              ) : (
                                <div className="flex items-center justify-between">
                                  <div>
                                    <p className="text-[10px] text-slate-400 font-medium">ค่าบำรุงการสอบ</p>
                                    <p className="text-md font-black text-indigo-600">{exam.price.toLocaleString()} ฿</p>
                                  </div>

                                  <div>
                                    {reg ? (
                                      reg.paymentStatus === 'Pending' ? (
                                        <button
                                          onClick={() => setPaymentModalData({
                                            type: 'Exam',
                                            refId: exam.id,
                                            title: exam.name,
                                            amount: exam.price
                                          })}
                                          className="bg-amber-50 text-amber-700 text-[10px] font-bold px-2.5 py-1.5 rounded-lg border border-amber-200 hover:bg-amber-100 transition-colors"
                                        >
                                          ส่งสลิปชำระเงิน
                                        </button>
                                      ) : reg.score !== null ? (
                                        <span className="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2.5 py-1.5 rounded-lg border border-emerald-100">
                                          บันทึกคะแนนสอบ: {reg.score}
                                        </span>
                                      ) : (
                                        <span className="bg-indigo-50 text-indigo-700 text-[10px] font-bold px-2.5 py-1.5 rounded-lg border border-indigo-100">
                                          ได้รับสิทธิ์เข้าสอบแล้ว
                                        </span>
                                      )
                                    ) : (
                                      <button
                                        onClick={() => handleExamRegister(exam)}
                                        className="bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg transition-all"
                                      >
                                        สมัครเข้ารอบสอบนี้
                                      </button>
                                    )}
                                  </div>
                                </div>
                              )}
                            </div>
                          </div>
                        );
                      })}
                    </div>
                  </div>
                </div>
              )}

              {/* TAB: Translation Service */}
              {activeTab === 'translation' && currentRole === 'user' && (
                <div className="space-y-6">
                  <div className="bg-gradient-to-br from-indigo-950 to-slate-900 text-white p-6 rounded-2xl shadow-md">
                    <h2 className="text-xl font-bold">ศูนย์แปลภาษาสากลแบบบูรณาการ</h2>
                    <p className="text-slate-300 text-xs mt-1 leading-relaxed">
                      ยื่นเอกสารเพื่อรับการประเมินราคาตามคำสั่งการให้บริการ แอดมินจะติดต่อส่งราคากลับเพื่อให้ลูกค้ายืนยันจ่ายค่าชำระผลงาน
                    </p>
                  </div>

                  <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* ยื่นแปล */}
                    <div className="lg:col-span-1 bg-white p-4 rounded-xl border border-slate-200 shadow-xs h-fit">
                      <h3 className="font-bold text-xs text-slate-800 mb-3 flex items-center gap-1">
                        <Upload className="h-4 w-4 text-indigo-600" />
                        ยื่นขอแปลเอกสารใหม่
                      </h3>
                      <form onSubmit={handleTranslationSubmit} className="space-y-3">
                        <div>
                          <label className="block text-[10px] font-bold text-slate-500 mb-1">ชื่อเอกสารที่จะส่งตรวจ</label>
                          <input 
                            type="text" 
                            placeholder="เช่น contract_v2_final.docx"
                            value={translationFile}
                            onChange={(e) => setTranslationFile(e.target.value)}
                            className="w-full text-xs p-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none"
                            required
                          />
                        </div>
                        <div className="grid grid-cols-2 gap-2">
                          <div>
                            <label className="block text-[10px] font-bold text-slate-500 mb-1">ภาษาต้นทาง</label>
                            <select 
                              value={sourceLang} 
                              onChange={(e) => setSourceLang(e.target.value)}
                              className="w-full text-xs p-2 rounded-lg border bg-white"
                            >
                              <option>ไทย</option>
                              <option>อังกฤษ</option>
                              <option>ลาว</option>
                            </select>
                          </div>
                          <div>
                            <label className="block text-[10px] font-bold text-slate-500 mb-1">ภาษาปลายทาง</label>
                            <select 
                              value={targetLang} 
                              onChange={(e) => setTargetLang(e.target.value)}
                              className="w-full text-xs p-2 rounded-lg border bg-white"
                            >
                              <option>อังกฤษ</option>
                              <option>ไทย</option>
                              <option>ลาว</option>
                            </select>
                          </div>
                        </div>
                        <button 
                          type="submit"
                          className="w-full bg-indigo-600 text-white font-bold py-2 px-3 rounded-xl text-xs hover:bg-indigo-700 transition-colors"
                        >
                          ส่งประเมินราคา
                        </button>
                      </form>
                    </div>

                    {/* รายงานประวัติคำแปล */}
                    <div className="lg:col-span-2 bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                      <h3 className="font-bold text-xs text-slate-800 mb-3">ประวัติการขอแปลและสถานะ</h3>
                      <div className="space-y-3">
                        {translationRequests.filter(r => r.userId === currentUser.email).map(req => {
                          const payment = payments.find(p => p.type === 'Translation' && p.refId === req.id);
                          return (
                            <div key={req.id} className="p-3 bg-slate-50 border rounded-xl flex justify-between items-center text-xs">
                              <div className="space-y-1">
                                <p className="font-bold text-slate-800">{req.fileName}</p>
                                <p className="text-[10px] text-slate-500">แปล: {req.sourceLang} → {req.targetLang}</p>
                                <p className="text-[10px]">
                                  สถานะคำสั่ง: 
                                  <span className={`ml-1 font-bold ${
                                    req.status === 'Completed' ? 'text-emerald-600' : 'text-indigo-600 animate-pulse'
                                  }`}>{req.status}</span>
                                </p>
                              </div>
                              <div className="text-right space-y-1.5">
                                {req.estimatedPrice ? (
                                  <div>
                                    <p className="text-[10px] text-slate-400">ราคาประเมิน</p>
                                    <p className="font-extrabold text-indigo-600">{req.estimatedPrice} ฿</p>
                                  </div>
                                ) : (
                                  <span className="text-[10px] text-slate-400 italic">รอพิจารณาประเมินราคา...</span>
                                )}

                                {req.status === 'Quote_Sent' && (!payment || payment.status === 'Pending') && (
                                  <button
                                    onClick={() => setPaymentModalData({
                                      type: 'Translation',
                                      refId: req.id,
                                      title: `งานแปล: ${req.fileName}`,
                                      amount: req.estimatedPrice
                                    })}
                                    className="bg-amber-500 text-slate-900 font-bold px-2 py-1 rounded text-[10px] hover:bg-amber-400 transition-colors"
                                  >
                                    แนบสลิปชำระเงิน
                                  </button>
                                )}

                                {req.status === 'Completed' && req.translatedFile && (
                                  <a 
                                    href={`#download-${req.id}`}
                                    onClick={(e) => { e.preventDefault(); showToast("ดาวน์โหลดไฟล์งานแปลสำเร็จ!"); }}
                                    className="inline-flex items-center gap-1 bg-emerald-600 text-white font-bold px-2 py-1 rounded text-[10px] hover:bg-emerald-700 transition-colors"
                                  >
                                    <Download className="h-3 w-3" /> ดาวน์โหลดงานแปล
                                  </a>
                                )}
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* TAB: Certificates and My Portfolio */}
              {activeTab === 'my-portfolio' && currentRole === 'user' && (
                <div className="space-y-6">
                  <div className="bg-gradient-to-br from-indigo-900 to-indigo-950 text-white p-6 rounded-2xl shadow-md">
                    <h2 className="text-xl font-bold">แผงเกียรติประวัติและใบรับรองอิเล็กทรอนิกส์</h2>
                    <p className="text-indigo-200 text-xs mt-1">
                      ประมวลผลประวัติวิชาการ คอร์สเรียนที่สำเร็จ และพิมพ์เอกสาร Digital Certificate แบบลงตราอย่างเป็นทางการของศูนย์วิชาการ
                    </p>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {/* ใบรับรองคอร์สอบรม */}
                    <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                      <h3 className="font-extrabold text-xs text-slate-800 mb-3 flex items-center gap-1">
                        <BookOpen className="h-4.5 w-4.5 text-indigo-600" />
                        ใบประกาศผ่านการอบรมภาษาศาสตร์
                      </h3>
                      <div className="space-y-2">
                        {enrollments.filter(e => e.userId === currentUser.email && e.status === 'Passed').length === 0 ? (
                          <p className="text-center py-6 text-slate-400 text-xs italic">ไม่มีประวัติการอบรมที่ผ่านเกณฑ์</p>
                        ) : (
                          enrollments.filter(e => e.userId === currentUser.email && e.status === 'Passed').map(enr => {
                            const c = courses.find(item => item.id === enr.courseId);
                            return (
                              <div key={enr.id} className="p-3 bg-slate-50 border border-slate-100 rounded-xl flex justify-between items-center text-xs">
                                <div>
                                  <p className="font-bold text-slate-800">{c?.name}</p>
                                  <p className="text-[10px] text-slate-500">รหัสระดับขั้นเรียน: {c?.id}</p>
                                </div>
                                <button
                                  onClick={() => setViewingCertificate({
                                    title: certTemplate.title,
                                    subtitle: certTemplate.subTitle,
                                    name: currentUser.name,
                                    detail: `ได้เรียนและผ่านเกณฑ์ประเมินโครงสร้างหลักสูตรวิชา: ${c?.name} (${c?.id}) อย่างสมบูรณ์การฝึกอบรมสากล`,
                                    sign: certTemplate.signatory1,
                                    signSub: certTemplate.signatory1Sub
                                  })}
                                  className="bg-indigo-600 text-white font-bold px-2.5 py-1.5 rounded-lg text-[10px] hover:bg-indigo-700 transition-colors flex items-center gap-1"
                                >
                                  <Award className="h-3 w-3" /> เปิดดูใบประกาศ
                                </button>
                              </div>
                            );
                          })
                        )}
                      </div>
                    </div>

                    {/* ใบรับรองผลสอบ */}
                    <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                      <h3 className="font-extrabold text-xs text-slate-800 mb-3 flex items-center gap-1">
                        <GraduationCap className="h-4.5 w-4.5 text-indigo-600" />
                        ใบประกาศผลคะแนนประเมินการสอบ
                      </h3>
                      <div className="space-y-2">
                        {examRegistrations.filter(r => r.userId === currentUser.email && r.score !== null).length === 0 ? (
                          <p className="text-center py-6 text-slate-400 text-xs italic">ไม่มีข้อมูลผลสอบหรือประวัติเข้าสอบล่าสุด</p>
                        ) : (
                          examRegistrations.filter(r => r.userId === currentUser.email && r.score !== null).map(reg => {
                            const ex = exams.find(item => item.id === reg.examId);
                            return (
                              <div key={reg.id} className="p-3 bg-slate-50 border border-slate-100 rounded-xl flex justify-between items-center text-xs">
                                <div>
                                  <p className="font-bold text-slate-800">{ex?.name}</p>
                                  <p className="text-[10px] text-slate-500">รอบวันทดสอบ: {ex?.date}</p>
                                  <p className="text-[10px] text-indigo-600 font-bold">คะแนนรวมที่ได้: {reg.score} คะแนน</p>
                                </div>
                                <button
                                  onClick={() => setViewingCertificate({
                                    title: 'OFFICIAL TEST CERTIFICATE',
                                    subtitle: 'ผลประเมินทักษะทางภาษาได้รับการรับรองอย่างเป็นทางการของ',
                                    name: currentUser.name,
                                    detail: `ได้เข้าร่วมการทดสอบ ${ex?.type} ประจำรอบปีวิชาการสถาบัน และสามารถประเมินผ่านเกณฑ์คะแนนวัดระดับที่: ${reg.score} คะแนน`,
                                    sign: certTemplate.signatory1,
                                    signSub: certTemplate.signatory1Sub
                                  })}
                                  className="bg-indigo-600 text-white font-bold px-2.5 py-1.5 rounded-lg text-[10px] hover:bg-indigo-700 transition-colors flex items-center gap-1"
                                >
                                  <Award className="h-3 w-3" /> เปิดดูผลสอบ
                                </button>
                              </div>
                            );
                          })
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              )}

            </div>
          )}

          {/* =========================================
              VIEW: ADMIN SEGMENT
             ========================================= */}
          {currentRole === 'admin' && (
            <div className="space-y-6">
              
              {/* ADMIN TAB 3.6: STATS / DASHBOARD */}
              {adminTab === 'stats' && (
                <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-6">
                  <div className="border-b pb-3">
                    <h2 className="text-sm font-extrabold text-slate-800 flex items-center gap-1">
                      <TrendingUp className="h-4.5 w-4.5 text-indigo-600" />
                      ศูนย์วิเคราะห์ข้อมูลและสถิติบัญชีงานบริการ (CARS Dashboard)
                    </h2>
                    <p className="text-[10px] text-slate-500">ประมวลผลสถิติ ข้อมูลการประเมินวิชาการ และสรุปค่าธรรมเนียมรวมที่อนุมัติแล้ว</p>
                  </div>

                  {/* STAT CARDS */}
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div className="p-4 bg-slate-50 border rounded-xl">
                      <p className="text-[9px] text-slate-400 font-bold uppercase tracking-wider">รายได้ที่อนุมัติสะสม</p>
                      <p className="text-lg font-black text-indigo-600 mt-1">
                        {payments.filter(p => p.status === 'Approved').reduce((acc, curr) => acc + curr.amount, 0).toLocaleString()} ฿
                      </p>
                    </div>
                    <div className="p-4 bg-slate-50 border rounded-xl">
                      <p className="text-[9px] text-slate-400 font-bold uppercase tracking-wider">บัญชีผู้ลงทะเบียน</p>
                      <p className="text-lg font-black text-slate-800 mt-1">{usersList.length} รายชื่อ</p>
                    </div>
                    <div className="p-4 bg-slate-50 border rounded-xl">
                      <p className="text-[9px] text-slate-400 font-bold uppercase tracking-wider">การเรียนทั้งหมด</p>
                      <p className="text-lg font-black text-emerald-600 mt-1">{enrollments.length} คลาส</p>
                    </div>
                    <div className="p-4 bg-slate-50 border rounded-xl">
                      <p className="text-[9px] text-slate-400 font-bold uppercase tracking-wider">งานแปลเอกสาร</p>
                      <p className="text-lg font-black text-amber-600 mt-1">{translationRequests.length} ใบงาน</p>
                    </div>
                  </div>

                  {/* VISUAL MOCK BAR CHART */}
                  <div className="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                    <h4 className="font-bold text-xs text-slate-700">สัดส่วนผู้เรียนรายภาษา (คอร์สอบรม)</h4>
                    <div className="space-y-2">
                      <div>
                        <div className="flex justify-between text-[10px] text-slate-500 mb-1">
                          <span>ภาษาไทยเพื่อการสื่อสาร</span>
                          <span>{enrollments.filter(e => e.courseId.startsWith('TH')).length} คลาส</span>
                        </div>
                        <div className="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                          <div className="bg-indigo-600 h-full rounded-full" style={{ width: '65%' }}></div>
                        </div>
                      </div>
                      <div>
                        <div className="flex justify-between text-[10px] text-slate-500 mb-1">
                          <span>อังกฤษสื่อสารวิชาการ</span>
                          <span>{enrollments.filter(e => e.courseId.startsWith('EN')).length} คลาส</span>
                        </div>
                        <div className="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                          <div className="bg-amber-500 h-full rounded-full" style={{ width: '45%' }}></div>
                        </div>
                      </div>
                      <div>
                        <div className="flex justify-between text-[10px] text-slate-500 mb-1">
                          <span>ภาษาลาวขั้นพื้นฐาน/สูง</span>
                          <span>{enrollments.filter(e => e.courseId.startsWith('LA')).length} คลาส</span>
                        </div>
                        <div className="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                          <div className="bg-emerald-600 h-full rounded-full" style={{ width: '15%' }}></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* ADMIN TAB: SLIP APPROVAL */}
              {adminTab === 'slips' && (
                <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                  <div className="border-b pb-2">
                    <h2 className="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                      <DollarSign className="h-4.5 w-4.5 text-indigo-600" />
                      ตรวจสอบและอนุมัติสลิปโอนเงิน (Slip Verification Queue)
                    </h2>
                    <p className="text-[10px] text-slate-500">ตรวจสอบยอดนำส่งหลักฐานและอนุมัติสิทธิ์เข้าเรียน/รอบสอบของนักศึกษา</p>
                  </div>

                  <div className="space-y-2">
                    {payments.filter(p => p.status === 'Pending').length === 0 ? (
                      <p className="text-center py-8 text-slate-400 text-xs italic bg-slate-50 rounded-xl border border-dashed">
                        ไม่มีคิวหลักฐานสลิปค้างรอตรวจสอบในปัจจุบัน
                      </p>
                    ) : (
                      payments.filter(p => p.status === 'Pending').map(pay => (
                        <div key={pay.id} className="p-4 bg-slate-50 rounded-xl border flex flex-col md:flex-row justify-between items-start md:items-center gap-4 text-xs">
                          <div>
                            <div className="flex items-center gap-2 mb-1.5">
                              <span className="bg-indigo-100 text-indigo-800 text-[9px] font-bold px-1.5 py-0.5 rounded">
                               ประเภท: {pay.type}
                              </span>
                              <span className="font-mono text-[9px] text-slate-400">ID: {pay.refId}</span>
                            </div>
                            <p className="font-semibold text-slate-800">ผู้โอน: {pay.userId}</p>
                            <p className="text-[10px] text-slate-500">รูปไฟล์สลิป: {pay.slip || 'ยังไม่ระบุชื่อสลิป'}</p>
                            <p className="font-extrabold text-indigo-600 mt-1">จำนวนที่โอน: {pay.amount.toLocaleString()} ฿</p>
                          </div>

                          <div className="flex gap-2 w-full md:w-auto">
                            <button
                              onClick={() => adminApprovePayment(pay)}
                              className="flex-1 md:flex-none bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-1.5 px-3 rounded-xl text-[10px] transition-colors"
                            >
                              อนุมัติรับเงิน
                            </button>
                            <button
                              onClick={() => {
                                setPayments(prev => prev.filter(p => p.id !== pay.id));
                                showToast("ยกเลิกและปฏิเสธสลิปแล้ว");
                              }}
                              className="flex-1 md:flex-none bg-red-50 hover:bg-red-100 text-red-600 font-bold py-1.5 px-3 rounded-xl text-[10px] transition-colors"
                            >
                              ปฏิเสธสลิป
                            </button>
                          </div>
                        </div>
                      ))
                    )}
                  </div>
                </div>
              )}

              {/* ADMIN TAB 3.1: MANAGE ADMINS */}
              {adminTab === 'admins' && (
                <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                  <div className="border-b pb-2">
                    <h2 className="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                      <UserCheck className="h-4.5 w-4.5 text-indigo-600" />
                      ระบบจัดการเจ้าหน้าที่และแอดมิน (Admin & Staff Controls)
                    </h2>
                    <p className="text-[10px] text-slate-500">บริหารรายชื่อ มอบหมายสิทธิ์บทบาทหน้าที่ผู้ตรวจสอบระบบ</p>
                  </div>

                  <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <form onSubmit={handleAddAdmin} className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3 h-fit">
                      <h4 className="font-bold text-xs text-indigo-850">เพิ่มแอดมินใหม่</h4>
                      <div>
                        <label className="block text-[10px] text-slate-500 mb-1">ชื่อเจ้าหน้าที่</label>
                        <input 
                          type="text" 
                          value={newAdminName} 
                          onChange={(e) => setNewAdminName(e.target.value)}
                          className="w-full text-xs p-2 rounded-lg border bg-white" 
                          required 
                        />
                      </div>
                      <div>
                        <label className="block text-[10px] text-slate-500 mb-1">อีเมลทางการ (.ac.th)</label>
                        <input 
                          type="email" 
                          value={newAdminEmail} 
                          onChange={(e) => setNewAdminEmail(e.target.value)}
                          className="w-full text-xs p-2 rounded-lg border bg-white" 
                          required 
                        />
                      </div>
                      <div>
                        <label className="block text-[10px] text-slate-500 mb-1">สิทธิ์บทบาทหน้าที่</label>
                        <select 
                          value={newAdminRole} 
                          onChange={(e) => setNewAdminRole(e.target.value)}
                          className="w-full text-xs p-2 rounded-lg border bg-white"
                        >
                          <option>Super Admin</option>
                          <option>Translation Manager</option>
                          <option>Finance Officer</option>
                          <option>Staff</option>
                        </select>
                      </div>
                      <button 
                        type="submit" 
                        className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded-xl text-xs"
                      >
                        แต่งตั้งสิทธิ์
                      </button>
                    </form>

                    <div className="lg:col-span-2 overflow-x-auto border rounded-xl">
                      <table className="w-full text-left text-xs text-slate-700">
                        <thead className="bg-slate-100">
                          <tr className="border-b">
                            <th className="p-3">ชื่อเจ้าหน้าที่</th>
                            <th className="p-3">อีเมลติดต่อ</th>
                            <th className="p-3">บทบาทสิทธิ์</th>
                            <th className="p-3 text-center">คำสั่ง</th>
                          </tr>
                        </thead>
                        <tbody>
                          {adminsList.map(adm => (
                            <tr key={adm.id} className="border-b last:border-0 hover:bg-slate-50">
                              <td className="p-3 font-semibold">{adm.name}</td>
                              <td className="p-3 font-mono text-slate-500">{adm.email}</td>
                              <td className="p-3 font-bold text-indigo-700">{adm.role}</td>
                              <td className="p-3 text-center">
                                <button 
                                  onClick={() => {
                                    setAdminsList(prev => prev.filter(item => item.id !== adm.id));
                                    showToast("ลบสิทธิ์ผู้ดูแลระบบเรียบร้อย");
                                  }}
                                  className="text-red-500 hover:text-red-600 font-bold"
                                >
                                  ลบสิทธิ์
                                </button>
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              )}

              {/* ADMIN TAB 3.2: MANAGE USERS */}
              {adminTab === 'users' && (
                <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                  <div className="border-b pb-2">
                    <h2 className="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                      <Users className="h-4.5 w-4.5 text-indigo-600" />
                      ตารางรายชื่อบัญชีผู้เข้าลงเรียนและลงทะเบียน (User Profiles)
                    </h2>
                    <p className="text-[10px] text-slate-500">จัดการข้อมูลประวัติติดต่อ สิทธิ์บัญชีการเข้าประเมินผล</p>
                  </div>

                  <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <form onSubmit={handleAddUser} className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3 h-fit">
                      <h4 className="font-bold text-xs text-indigo-850">เพิ่มข้อมูลบัญชีผู้เรียน</h4>
                      <div>
                        <label className="block text-[10px] text-slate-500 mb-1">ชื่อผู้สมัครเรียน</label>
                        <input 
                          type="text" 
                          value={newUserName} 
                          onChange={(e) => setNewUserName(e.target.value)}
                          className="w-full text-xs p-2 rounded-lg border bg-white" 
                          required 
                        />
                      </div>
                      <div>
                        <label className="block text-[10px] text-slate-500 mb-1">อีเมลผู้สมัคร (ใช้ล็อกอิน)</label>
                        <input 
                          type="email" 
                          value={newUserEmail} 
                          onChange={(e) => setNewUserEmail(e.target.value)}
                          className="w-full text-xs p-2 rounded-lg border bg-white" 
                          required 
                        />
                      </div>
                      <div>
                        <label className="block text-[10px] text-slate-500 mb-1">เบอร์มือถือ</label>
                        <input 
                          type="text" 
                          value={newUserPhone} 
                          onChange={(e) => setNewUserPhone(e.target.value)}
                          className="w-full text-xs p-2 rounded-lg border bg-white" 
                        />
                      </div>
                      <button 
                        type="submit" 
                        className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded-xl text-xs"
                      >
                        บันทึกบัญชีใหม่
                      </button>
                    </form>

                    <div className="lg:col-span-2 overflow-x-auto border rounded-xl">
                      <table className="w-full text-left text-xs text-slate-700">
                        <thead className="bg-slate-100">
                          <tr className="border-b">
                            <th className="p-3">ชื่อ-สกุลผู้เรียน</th>
                            <th className="p-3">บัญชีเข้าใช้งาน (อีเมล)</th>
                            <th className="p-3">เบอร์ติดต่อ</th>
                            <th className="p-3 text-center">สิทธิ์ล็อกอิน</th>
                          </tr>
                        </thead>
                        <tbody>
                          {usersList.map(usr => (
                            <tr key={usr.email} className="border-b last:border-0 hover:bg-slate-50">
                              <td className="p-3 font-semibold">{usr.name}</td>
                              <td className="p-3 font-mono text-slate-500">{usr.email}</td>
                              <td className="p-3">{usr.phone}</td>
                              <td className="p-3 text-center">
                                <button 
                                  onClick={() => {
                                    setCurrentUser({ email: usr.email, name: usr.name, isVerified: true });
                                    showToast(`สวมสิทธิ์บัญชีผู้ใช้เป็น: ${usr.name}`);
                                  }}
                                  className="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-2 py-1 rounded text-[10px]"
                                >
                                  สวมสิทธิ์ทดสอบ
                                </button>
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              )}

              {/* ADMIN TAB 3.3: CURRICULUM MANAGEMENT */}
              {adminTab === 'courses-manage' && (
                <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-6">
                  <div className="border-b pb-2">
                    <h2 className="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                      <Sliders className="h-4.5 w-4.5 text-indigo-600" />
                      จัดการหลักสูตรและรอบจัดสอบประเมินภาษา (Curriculum Controls)
                    </h2>
                    <p className="text-[10px] text-slate-500">สร้าง แก้ไขรายละเอียดราคา และจัดการสิทธิ์ Prerequisite หลักสูตรของศูนย์ภาษา</p>
                  </div>

                  <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* จัดการหลักสูตรอบรม */}
                    <div className="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-4">
                      <h4 className="font-extrabold text-xs text-indigo-800 flex items-center gap-1">
                        <BookOpen className="h-4 w-4" /> เพิ่ม/แก้ไขหลักสูตรอบรม (ระดับขั้นเรียน)
                      </h4>

                      <form onSubmit={handleAddCourse} className="space-y-3">
                        <div className="grid grid-cols-2 gap-2">
                          <input 
                            type="text" 
                            placeholder="รหัส เช่น EN-01" 
                            value={courseForm.id}
                            onChange={(e) => setCourseForm({ ...courseForm, id: e.target.value })}
                            className="text-xs p-2 border bg-white rounded-lg w-full font-mono font-bold"
                            required
                          />
                          <input 
                            type="text" 
                            placeholder="ชื่อระดับการเรียน" 
                            value={courseForm.name}
                            onChange={(e) => setCourseForm({ ...courseForm, name: e.target.value })}
                            className="text-xs p-2 border bg-white rounded-lg w-full"
                            required
                          />
                        </div>
                        <div className="grid grid-cols-3 gap-2">
                          <select 
                            value={courseForm.lang}
                            onChange={(e) => setCourseForm({ ...courseForm, lang: e.target.value })}
                            className="text-xs p-2 border bg-white rounded-lg"
                          >
                            <option value="อังกฤษ">อังกฤษ</option>
                            <option value="ไทย">ไทย</option>
                            <option value="ลาว">ลาว</option>
                          </select>
                          <input 
                            type="number" 
                            placeholder="ระดับขั้น (1, 2)" 
                            value={courseForm.level}
                            onChange={(e) => setCourseForm({ ...courseForm, level: e.target.value })}
                            className="text-xs p-2 border bg-white rounded-lg w-full"
                            required
                          />
                          <input 
                            type="number" 
                            placeholder="ราคาค่าธรรมเนียม" 
                            value={courseForm.price}
                            onChange={(e) => setCourseForm({ ...courseForm, price: e.target.value })}
                            className="text-xs p-2 border bg-white rounded-lg w-full"
                            required
                          />
                        </div>
                        <input 
                          type="text" 
                          placeholder="รหัสวิชา Prerequisite ก่อนหน้า (เช่น EN-01)" 
                          value={courseForm.prerequisite}
                          onChange={(e) => setCourseForm({ ...courseForm, prerequisite: e.target.value })}
                          className="text-xs p-2 border bg-white rounded-lg w-full"
                        />
                        <button 
                          type="submit" 
                          className="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 rounded-xl"
                        >
                          บันทึกและอัปเดตหลักสูตรอบรม
                        </button>
                      </form>

                      {/* ตารางควบคุม พร้อมปุ่มลัด ซ่อน/แก้ไข */}
                      <div className="space-y-2 max-h-[160px] overflow-y-auto bg-white p-2 rounded-lg border">
                        {courses.map(c => {
                          const isHidden = hiddenCourses.includes(c.id);
                          return (
                            <div key={c.id} className="flex justify-between items-center text-[10px] p-2 border-b last:border-0 hover:bg-slate-50">
                              <div>
                                <p className={`font-bold ${isHidden ? 'line-through text-slate-400' : 'text-slate-800'}`}>
                                  {c.id}: {c.name}
                                </p>
                                {isHidden && <span className="text-[8px] text-red-500 font-bold">ซ่อนจากหน้าเว็บแล้ว</span>}
                              </div>
                              <div className="flex items-center gap-2">
                                <span className="font-mono text-slate-500">{c.price} ฿</span>
                                <button 
                                  onClick={() => handleToggleCourseVisibility(c.id)}
                                  className="p-1 hover:bg-slate-100 rounded"
                                  title={isHidden ? "แสดง" : "ซ่อน"}
                                >
                                  {isHidden ? <EyeOff className="h-3.5 w-3.5 text-red-500" /> : <Eye className="h-3.5 w-3.5 text-indigo-600" />}
                                </button>
                                <button 
                                  onClick={() => handleEditCourse(c)}
                                  className="p-1 hover:bg-slate-100 rounded text-amber-500"
                                >
                                  <Edit className="h-3.5 w-3.5" />
                                </button>
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    </div>

                    {/* จัดการรอบสอบภาษาอังกฤษ */}
                    <div className="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-4">
                      <h4 className="font-extrabold text-xs text-indigo-800 flex items-center gap-1">
                        <GraduationCap className="h-4 w-4" /> เพิ่ม/แก้ไขรอบจัดสอบวัดระดับภาษา
                      </h4>

                      <form onSubmit={handleAddExam} className="space-y-3">
                        <div className="grid grid-cols-2 gap-2">
                          <input 
                            type="text" 
                            placeholder="รหัส เช่น EX-IELTS" 
                            value={examForm.id}
                            onChange={(e) => setExamForm({ ...examForm, id: e.target.value })}
                            className="text-xs p-2 border bg-white rounded-lg w-full font-mono font-bold"
                            required
                          />
                          <input 
                            type="text" 
                            placeholder="ชื่อกลุ่มจัดสอบวัดระดับ" 
                            value={examForm.name}
                            onChange={(e) => setExamForm({ ...examForm, name: e.target.value })}
                            className="text-xs p-2 border bg-white rounded-lg w-full"
                            required
                          />
                        </div>
                        <div className="grid grid-cols-3 gap-2">
                          <select 
                            value={examForm.type}
                            onChange={(e) => setExamForm({ ...examForm, type: e.target.value })}
                            className="text-xs p-2 border bg-white rounded-lg"
                          >
                            <option value="TOEIC">TOEIC</option>
                            <option value="EPT">EPT</option>
                            <option value="IELTS">IELTS</option>
                          </select>
                          <input 
                            type="number" 
                            placeholder="ค่าบำรุงสอบ" 
                            value={examForm.price}
                            onChange={(e) => setExamForm({ ...examForm, price: e.target.value })}
                            className="text-xs p-2 border bg-white rounded-lg w-full"
                            required
                          />
                          <input 
                            type="date" 
                            value={examForm.date}
                            onChange={(e) => setExamForm({ ...examForm, date: e.target.value })}
                            className="text-xs p-2 border bg-white rounded-lg w-full"
                            required
                          />
                        </div>
                        <button 
                          type="submit" 
                          className="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 rounded-xl"
                        >
                          บันทึกและอัปเดตรอบจัดสอบ
                        </button>
                      </form>

                      {/* ตารางควบคุมรอบสอบ พร้อมปุ่มลัด ซ่อน/แก้ไข */}
                      <div className="space-y-2 max-h-[160px] overflow-y-auto bg-white p-2 rounded-lg border">
                        {exams.map(ex => {
                          const isHidden = hiddenExams.includes(ex.id);
                          return (
                            <div key={ex.id} className="flex justify-between items-center text-[10px] p-2 border-b last:border-0 hover:bg-slate-50">
                              <div>
                                <p className={`font-bold ${isHidden ? 'line-through text-slate-400' : 'text-slate-800'}`}>
                                  {ex.id}: {ex.name}
                                </p>
                                {isHidden && <span className="text-[8px] text-red-500 font-bold">ซ่อนจากหน้าเว็บแล้ว</span>}
                              </div>
                              <div className="flex items-center gap-2">
                                <span className="font-mono text-slate-500">{ex.date}</span>
                                <button 
                                  onClick={() => handleToggleExamVisibility(ex.id)}
                                  className="p-1 hover:bg-slate-100 rounded"
                                  title={isHidden ? "แสดง" : "ซ่อน"}
                                >
                                  {isHidden ? <EyeOff className="h-3.5 w-3.5 text-red-500" /> : <Eye className="h-3.5 w-3.5 text-indigo-600" />}
                                </button>
                                <button 
                                  onClick={() => handleEditExam(ex)}
                                  className="p-1 hover:bg-slate-100 rounded text-amber-500"
                                >
                                  <Edit className="h-3.5 w-3.5" />
                                </button>
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* ADMIN TAB 3.4: EMAIL TEMPLATES */}
              {adminTab === 'email-templates' && (
                <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                  <div className="border-b pb-2">
                    <h2 className="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                      <Mail className="h-4.5 w-4.5 text-indigo-600" />
                      จัดการข้อความและแม่แบบจดหมายส่งออก (Email System Templates)
                    </h2>
                    <p className="text-[10px] text-slate-500">ปรับแต่งข้อความ Dynamic แจ้งเตือนสิทธิประโยชน์ทางการให้ผู้สมัครประเมินผล</p>
                  </div>

                  <div className="space-y-4">
                    {Object.keys(emailTemplates).map(key => (
                      <div key={key} className="p-4 bg-slate-50 border rounded-xl space-y-2">
                        <p className="font-bold text-xs text-indigo-700 uppercase">ประเภทแจ้งเตือน: {key}</p>
                        <div>
                          <label className="block text-[10px] text-slate-400 mb-0.5">หัวเรื่องจดหมาย (Subject Line)</label>
                          <input 
                            type="text" 
                            value={emailTemplates[key].subject}
                            onChange={(e) => setEmailTemplates(prev => ({
                              ...prev,
                              [key]: { ...prev[key], subject: e.target.value }
                            }))}
                            className="w-full text-xs p-2 rounded-lg border bg-white font-semibold"
                          />
                        </div>
                        <div>
                          <label className="block text-[10px] text-slate-400 mb-0.5">เนื้อหาจดหมาย (Body Text)</label>
                          <textarea 
                            rows={2} 
                            value={emailTemplates[key].body}
                            onChange={(e) => setEmailTemplates(prev => ({
                              ...prev,
                              [key]: { ...prev[key], body: e.target.value }
                            }))}
                            className="w-full text-xs p-2 rounded-lg border bg-white"
                          />
                        </div>
                      </div>
                    ))}
                    <button 
                      onClick={() => showToast("บันทึกการตั้งค่าแม่แบบอีเมลสำเร็จ!")}
                      className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl text-xs"
                    >
                      บันทึกตั้งค่าแม่แบบจดหมายระบบ
                    </button>
                  </div>
                </div>
              )}

              {/* ADMIN TAB 3.5: CERTIFICATE DESIGNER */}
              {adminTab === 'cert-templates' && (
                <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                  <div className="border-b pb-2">
                    <h2 className="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                      <Award className="h-4.5 w-4.5 text-indigo-600" />
                      แบบฟอร์มดีไซเนอร์และลายเซ็นใบรับรองวิชาการ (Certificate Designer)
                    </h2>
                    <p className="text-[10px] text-slate-500">ปรับแต่งตำแหน่งและสีกรอบของใบประกาศเกียรติคุณวิชาการของสถาบัน</p>
                  </div>

                  <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div className="p-4 bg-slate-50 rounded-xl border space-y-3">
                      <h4 className="font-bold text-xs text-indigo-850">ตั้งค่าใบตราประกาศ</h4>
                      <div>
                        <label className="block text-[10px] text-slate-400 mb-0.5">หัวข้อใบรับรองสากล</label>
                        <input 
                          type="text" 
                          value={certTemplate.title}
                          onChange={(e) => setCertTemplate({ ...certTemplate, title: e.target.value })}
                          className="w-full text-xs p-2 rounded-lg border bg-white"
                        />
                      </div>
                      <div>
                        <label className="block text-[10px] text-slate-400 mb-0.5">คำอธิบายรายละเอียดเกียรติประวัติ</label>
                        <input 
                          type="text" 
                          value={certTemplate.subTitle}
                          onChange={(e) => setCertTemplate({ ...certTemplate, subTitle: e.target.value })}
                          className="w-full text-xs p-2 rounded-lg border bg-white"
                        />
                      </div>
                      <div className="grid grid-cols-2 gap-2">
                        <div>
                          <label className="block text-[10px] text-slate-400 mb-0.5">ชื่อผู้ลงตราหลัก</label>
                          <input 
                            type="text" 
                            value={certTemplate.signatory1}
                            onChange={(e) => setCertTemplate({ ...certTemplate, signatory1: e.target.value })}
                            className="w-full text-xs p-2 rounded-lg border bg-white"
                          />
                        </div>
                        <div>
                          <label className="block text-[10px] text-slate-400 mb-0.5">สีเส้นขอบใบรับรอง</label>
                          <input 
                            type="color" 
                            value={certTemplate.borderColor}
                            onChange={(e) => setCertTemplate({ ...certTemplate, borderColor: e.target.value })}
                            className="w-full h-8 rounded-lg border bg-white cursor-pointer"
                          />
                        </div>
                      </div>
                      <button 
                        onClick={() => showToast("อัปเดตแม่แบบและลายเซ็นสำเร็จ!")}
                        className="w-full bg-indigo-600 text-white font-bold py-2 rounded-xl text-xs"
                      >
                        อัปเดตโครงร่างใบรับรองวิชาการ
                      </button>
                    </div>

                    {/* MOCKUP MINI PREVIEW */}
                    <div className="p-4 bg-slate-100 rounded-xl border flex flex-col justify-center items-center">
                      <div 
                        className="p-4 rounded-xl border-4 text-center max-w-sm w-full bg-white space-y-2 relative"
                        style={{ borderColor: certTemplate.borderColor }}
                      >
                        <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{certTemplate.title}</p>
                        <p className="text-[8px] text-slate-500 leading-normal">{certTemplate.subTitle}</p>
                        <p className="text-xs font-black text-slate-800 underline">นาย สมชาย รักเรียน</p>
                        <div className="border-t border-slate-200 pt-2 text-[8px] text-slate-400 mt-2">
                          <p className="font-bold">{certTemplate.signatory1}</p>
                          <p>{certTemplate.signatory1Sub}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* ADMIN TAB 3.7: SYSTEM MANUAL */}
              {adminTab === 'manual' && (
                <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4 text-xs leading-relaxed text-slate-700">
                  <div className="border-b pb-2">
                    <h2 className="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                      <HelpCircle className="h-4.5 w-4.5 text-indigo-600" />
                      คู่มือการใช้และสถาปัตยกรรมระบบสำหรับแอดมิน (Operator Handbook)
                    </h2>
                    <p className="text-[10px] text-slate-500">วิธีการทำงาน โครงสร้างนำเข้า และความสัมพันธ์ของโครงข่ายระบบ</p>
                  </div>

                  <div className="space-y-4">
                    <div className="p-4 bg-slate-50 border rounded-xl">
                      <p className="font-bold text-indigo-700 text-xs mb-1">1. ลำดับนำเข้ารายชื่อคะแนนด้วย Excel</p>
                      <p className="text-[11px] text-slate-500">
                        แอดมินสามารถบันทึกนำเข้ารอบผลคะแนนโดยใช้แท็บ "จัดการข้อมูลการสอบ" ทำการจัดเตรียม CSV ฟอร์แมต <code className="bg-slate-200 px-1 rounded">อีเมล,คะแนน</code> โดยระบบหลังบ้านจะจับคู่อีเมลเข้ากับระบบและจัดส่งอีเมลและปลดล็อกใบประกาศอัตโนมัติ
                      </p>
                    </div>
                    <div className="p-4 bg-slate-50 border rounded-xl">
                      <p className="font-bold text-indigo-700 text-xs mb-1">2. ตรรกะล็อกความพร้อมเรียน (Prerequisite Core Logic)</p>
                      <p className="text-[11px] text-slate-500">
                        วิชาระดับขั้นเรียน 2 บังคับผ่านระดับขั้นเรียน 1 ก่อนเสมอ โดยพิจารณาจากตารางตรรกะประเมินผล <code className="bg-slate-200 px-1 rounded">status === "Passed"</code> ในระบบเป็นแกนหลัก และจะซ่อนการสมัครหากผ่านเกณฑ์แล้ว
                      </p>
                    </div>
                  </div>
                </div>
              )}

              {/* ========================================================================
                  3.1 [NEW REPLACEMENT] จัดการข้อมูลการสอบ (Exam Registrant Selector & CSV Import)
                  ======================================================================== */}
              {adminTab === 'manage-exams' && (
                <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-5 animate-in fade-in duration-200">
                  <div className="border-b pb-3 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                      <h3 className="font-bold text-sm text-slate-850 flex items-center gap-1.5">
                        <GraduationCap className="h-4.5 w-4.5 text-indigo-600" />
                        จัดการข้อมูลการสอบ (Exam Registrations & CSV Parser)
                      </h3>
                      <p className="text-[10px] text-slate-500">เลือกดูรายชื่อผู้สมัครรายกลุ่มจัดสอบวัดผล และประเมินอัปโหลดคะแนนแบบคลาส</p>
                    </div>

                    <div className="flex items-center gap-2">
                      <label className="text-[11px] font-bold text-slate-600 whitespace-nowrap">เลือกรอบการสอบ:</label>
                      <select 
                        value={selectedExamId}
                        onChange={(e) => setSelectedExamId(e.target.value)}
                        className="text-xs p-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold cursor-pointer"
                      >
                        {exams.map(ex => (
                          <option key={ex.id} value={ex.id}>{ex.name} ({ex.type})</option>
                        ))}
                      </select>
                    </div>
                  </div>

                  <div className="bg-slate-50 p-4 rounded-xl border border-slate-150">
                    <h4 className="font-extrabold text-xs text-slate-700 mb-3 flex items-center justify-between">
                      <span>รายชื่อผู้สมัครรอบสอบ: {exams.find(e => e.id === selectedExamId)?.name}</span>
                      <span className="bg-indigo-100 text-indigo-700 font-mono px-2 py-0.5 rounded text-[10px]">
                        ทั้งหมด {examRegistrations.filter(r => r.examId === selectedExamId).length} รายการ
                      </span>
                    </h4>

                    {examRegistrations.filter(r => r.examId === selectedExamId).length === 0 ? (
                      <p className="text-center py-6 text-slate-400 text-xs italic bg-white border rounded-xl">ไม่มีรายชื่อผู้สมัครสอบสำหรับรอบการสอบนี้</p>
                    ) : (
                      <div className="overflow-x-auto bg-white rounded-xl border border-slate-150 shadow-inner">
                        <table className="w-full text-left text-xs text-slate-700 border-collapse">
                          <thead>
                            <tr className="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                              <th className="p-3">อีเมลบัญชีผู้สอบ</th>
                              <th className="p-3">ชื่อผู้สอบ</th>
                              <th className="p-3 text-center">สถานะชำระเงิน</th>
                              <th className="p-3 text-center">คะแนนสอบปัจจุบัน</th>
                              <th className="p-3 text-center">สิทธิ์ใบประกาศ</th>
                            </tr>
                          </thead>
                          <tbody>
                            {examRegistrations.filter(r => r.examId === selectedExamId).map(reg => {
                              const usrInfo = usersList.find(u => u.email === reg.userId);
                              return (
                                <tr key={reg.id} className="border-b border-slate-100 hover:bg-slate-50/50">
                                  <td className="p-3 font-mono font-bold text-slate-600">{reg.userId}</td>
                                  <td className="p-3 font-semibold">{usrInfo?.name || "ไม่ระบุชื่อ"}</td>
                                  <td className="p-3 text-center">
                                    <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                                      reg.paymentStatus === 'Approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'
                                    }`}>
                                      {reg.paymentStatus}
                                    </span>
                                  </td>
                                  <td className="p-3 text-center font-bold text-indigo-600 font-mono">
                                    {reg.score !== null ? `${reg.score} คะแนน` : "ยังไม่มีคะแนน"}
                                  </td>
                                  <td className="p-3 text-center">
                                    {reg.certIssued ? (
                                      <span className="text-emerald-600 font-semibold flex items-center justify-center gap-0.5 text-[10px]">
                                        <Check className="h-3 w-3" /> ออกใบรับรองแล้ว
                                      </span>
                                    ) : (
                                      <span className="text-slate-400 italic text-[10px]">ยังไม่ได้ออก</span>
                                    )}
                                  </td>
                                </tr>
                              );
                            })}
                          </tbody>
                        </table>
                      </div>
                    )}
                  </div>

                  {/* IMPORT EXCEL SECTION */}
                  <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                    <h4 className="font-extrabold text-xs text-indigo-800 flex items-center gap-1.5">
                      <FileUp className="h-4 w-4" />
                      นำเข้าคะแนนสอบด้วยไฟล์ Excel (CSV Format) สำหรับรอบสอบที่เลือก
                    </h4>
                    <p className="text-[10px] text-slate-500 leading-relaxed">
                      วางโครงสร้างข้อมูลรายชื่อเพื่อประมวลผลคะแนนเฉพาะสอบรอบ <strong className="text-slate-800">[{exams.find(e => e.id === selectedExamId)?.name}]</strong> รูปแบบข้อมูล: <code className="bg-indigo-50 text-indigo-600 px-1 py-0.5 rounded font-mono">อีเมล,คะแนนที่สอบได้</code> (เช่น <code className="bg-white p-0.5 border rounded font-mono">somchai.dev@gmail.com,780</code>)
                    </p>
                    <textarea
                      rows={3}
                      value={csvText}
                      onChange={(e) => setCsvText(e.target.value)}
                      className="w-full text-[10px] p-2.5 font-mono rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                      placeholder="อีเมลผู้สอบ,คะแนน"
                    />
                    <button
                      onClick={handleExamScoreImportExcel}
                      className="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded-xl transition-all shadow-xs flex items-center justify-center gap-1.5"
                    >
                      <Plus className="h-4 w-4" /> นำเข้า Excel เข้ารอบจัดสอบรหัส {selectedExamId}
                    </button>
                  </div>
                </div>
              )}

              {/* ========================================================================
                  3.2 [NEW REPLACEMENT] จัดการข้อมูลอบรม (Course Enrollment Table & Pass/Fail)
                  ======================================================================== */}
              {adminTab === 'manage-courses' && (
                <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-5 animate-in fade-in duration-200">
                  <div className="border-b pb-3 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                      <h3 className="font-bold text-sm text-slate-850 flex items-center gap-1.5">
                        <BookOpen className="h-4.5 w-4.5 text-indigo-600" />
                        จัดการข้อมูลอบรม (Course Enrollment List & Assessment)
                      </h3>
                      <p className="text-[10px] text-slate-500">เลือกดูรายชื่อนักศึกษาผู้สมัคร และประเมินผ่านหลักสูตรเพื่อปลดสิทธิ์ prerequisite</p>
                    </div>

                    <div className="flex items-center gap-2">
                      <label className="text-[11px] font-bold text-slate-600 whitespace-nowrap">เลือกคอร์สอบรม:</label>
                      <select 
                        value={selectedCourseId}
                        onChange={(e) => setSelectedCourseId(e.target.value)}
                        className="text-xs p-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold cursor-pointer"
                      >
                        {courses.map(c => (
                          <option key={c.id} value={c.id}>{c.id}: {c.name}</option>
                        ))}
                      </select>
                    </div>
                  </div>

                  <div className="bg-slate-50 p-4 rounded-xl border border-slate-150">
                    <h4 className="font-extrabold text-xs text-slate-700 mb-3 flex items-center justify-between">
                      <span>รายชื่อผู้ลงทะเบียนเรียนวิชา: {courses.find(c => c.id === selectedCourseId)?.name}</span>
                      <span className="bg-indigo-100 text-indigo-700 font-mono px-2 py-0.5 rounded text-[10px]">
                        ทั้งหมด {enrollments.filter(e => e.courseId === selectedCourseId).length} รายการ
                      </span>
                    </h4>

                    {enrollments.filter(e => e.courseId === selectedCourseId).length === 0 ? (
                      <p className="text-center py-6 text-slate-400 text-xs italic bg-white border rounded-xl">ไม่มีรายชื่อนักศึกษาผู้ลงเรียนในคอร์สนี้</p>
                    ) : (
                      <div className="overflow-x-auto bg-white rounded-xl border border-slate-150 shadow-inner">
                        <table className="w-full text-left text-xs text-slate-700 border-collapse">
                          <thead>
                            <tr className="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                              <th className="p-3">อีเมลบัญชีผู้เรียน</th>
                              <th className="p-3">ชื่อนักเรียน</th>
                              <th className="p-3 text-center">สถานะชำระเงิน</th>
                              <th className="p-3 text-center">สถานะการอบรม</th>
                              <th className="p-3 text-center">ประเมินตัดเกรด</th>
                            </tr>
                          </thead>
                          <tbody>
                            {enrollments.filter(e => e.courseId === selectedCourseId).map(enr => {
                              const usrInfo = usersList.find(u => u.email === enr.userId);
                              return (
                                <tr key={enr.id} className="border-b border-slate-100 hover:bg-slate-50/50">
                                  <td className="p-3 font-mono font-bold text-slate-600">{enr.userId}</td>
                                  <td className="p-3 font-semibold">{usrInfo?.name || "ไม่ระบุชื่อ"}</td>
                                  <td className="p-3 text-center">
                                    <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                                      enr.paymentStatus === 'Approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'
                                    }`}>
                                      {enr.paymentStatus}
                                    </span>
                                  </td>
                                  <td className="p-3 text-center">
                                    <span className={`px-2 py-0.5 rounded font-bold text-[10px] ${
                                      enr.status === 'Passed' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' :
                                      enr.status === 'Failed' ? 'bg-red-100 text-red-800 border border-red-200' :
                                      enr.status === 'Studying' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600'
                                    }`}>
                                      {enr.status}
                                    </span>
                                  </td>
                                  <td className="p-3 text-center">
                                    <div className="flex gap-1 justify-center">
                                      <button
                                        onClick={() => updateCourseGrade(enr.id, 'Passed')}
                                        className="bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold px-2 py-1 rounded transition-colors flex items-center gap-0.5"
                                        title="ผ่านการประเมิน"
                                      >
                                        <Check className="h-3 w-3" /> ผ่าน
                                      </button>
                                      <button
                                        onClick={() => updateCourseGrade(enr.id, 'Failed')}
                                        className="bg-red-50 hover:bg-red-100 text-red-600 text-[10px] font-bold px-2 py-1 rounded transition-colors"
                                        title="ไม่ผ่านการประเมิน"
                                      >
                                        ไม่ผ่าน
                                      </button>
                                    </div>
                                  </td>
                                </tr>
                              );
                            })}
                          </tbody>
                        </table>
                      </div>
                    )}
                  </div>
                </div>
              )}

              {/* ADMIN TAB: TRANSLATION QUOTES & FILE DISPATCH */}
              {adminTab === 'translation-quotes' && (
                <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4 animate-in fade-in duration-200">
                  <div className="border-b pb-2">
                    <h2 className="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                      <FileText className="h-4.5 w-4.5 text-indigo-600" />
                      ศูนย์ประเมินเอกสารและส่งมอบงานแปล (Translation Quotes & Dispatch)
                    </h2>
                    <p className="text-[10px] text-slate-500">ประเมินส่งอัตราค่าบริการ และแนบไฟล์จัดส่งผลแปลคืนแก่สมาชิกลูกค้า</p>
                  </div>

                  <div className="space-y-3">
                    {translationRequests.length === 0 ? (
                      <p className="text-center py-6 text-slate-400 text-xs italic">ไม่มีประวัติความต้องการคำสั่งแปลงานเอกสาร</p>
                    ) : (
                      translationRequests.map(req => (
                        <div key={req.id} className="p-4 bg-slate-50 border rounded-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4 text-xs">
                          <div>
                            <div className="flex items-center gap-1.5 mb-1">
                              <span className="bg-indigo-100 text-indigo-700 text-[9px] font-black px-2 py-0.5 rounded">รหัสคำขอ: #{req.id}</span>
                              <span className="text-slate-400 font-mono text-[9px]">ผู้ยื่น: {req.userId}</span>
                            </div>
                            <p className="font-bold text-slate-800">ชื่อเอกสาร: {req.fileName}</p>
                            <p className="text-[10px] text-slate-500">คู่ภาษาแปล: {req.sourceLang} แปลเป็น {req.targetLang}</p>
                            <p className="text-[10px] font-bold text-indigo-600 mt-1">สถานะคำสั่ง: {req.status}</p>
                          </div>

                          <div className="w-full md:w-auto bg-white p-3 rounded-xl border space-y-2">
                            {req.status === 'Submitted' && (
                              <div className="space-y-2">
                                <p className="font-bold text-[10px] text-indigo-700">ระบุใบเสนอราคางานแปล</p>
                                <div className="grid grid-cols-2 gap-1.5">
                                  <input 
                                    type="number" 
                                    id={`price-${req.id}`} 
                                    placeholder="ราคาประเมิน (บาท)" 
                                    className="text-xs p-1.5 border rounded" 
                                  />
                                  <input 
                                    type="number" 
                                    id={`days-${req.id}`} 
                                    placeholder="ระยะเวลาแปล (วัน)" 
                                    className="text-xs p-1.5 border rounded" 
                                  />
                                </div>
                                <button
                                  onClick={() => {
                                    const p = document.getElementById(`price-${req.id}`).value;
                                    const d = document.getElementById(`days-${req.id}`).value;
                                    if(p && d) submitTranslationQuote(req.id, p, d);
                                  }}
                                  className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-1 px-3 rounded text-[10px]"
                                >
                                  ส่งประเมินราคา
                                </button>
                              </div>
                            )}

                            {req.status === 'Translating' && (
                              <div className="space-y-1.5">
                                <p className="font-bold text-[10px] text-emerald-700">จ่ายค่าแปลสำเร็จ - แนบไฟล์แปลเสร็จเพื่อส่งมอบ</p>
                                <input 
                                  type="text" 
                                  id={`mockLink-${req.id}`} 
                                  placeholder="ชื่อเอกสารสำเร็จรูป เช่น translated_docs.pdf" 
                                  className="text-xs p-1.5 border rounded w-full" 
                                />
                                <button
                                  onClick={() => {
                                    const val = document.getElementById(`mockLink-${req.id}`).value;
                                    deliverTranslation(req.id, val);
                                  }}
                                  className="w-full bg-emerald-600 text-white font-bold py-1 px-3 rounded text-[10px]"
                                >
                                  ส่งมอบงานสำเร็จ
                                </button>
                              </div>
                            )}

                            {req.status === 'Completed' && (
                              <p className="text-[10px] text-emerald-600 font-bold text-center">ดำเนินการส่งมอบคำแปลแก่ผู้เรียนครบถ้วน</p>
                            )}
                          </div>
                        </div>
                      ))
                    )}
                  </div>
                </div>
              )}

            </div>
          )}

        </main>

      </div>

      {/* MODAL: PAYMENT ATTACHMENT WITH PROMPTPAY QR */}
      {paymentModalData && (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex justify-center items-center p-4 z-50 animate-in fade-in duration-200">
          <div className="bg-white p-5 rounded-2xl max-w-sm w-full shadow-2xl border border-slate-200 animate-in zoom-in-95 duration-150 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-start mb-3">
              <div>
                <h3 className="font-extrabold text-indigo-600 text-sm">อัปโหลดสลิปแจ้งยืนยันยอดเงิน (CARS-HUSOC)</h3>
                <p className="text-[10px] text-slate-500 mt-0.5">{paymentModalData.title}</p>
              </div>
              <button 
                onClick={() => setPaymentModalData(null)}
                className="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
              >
                <X className="h-4.5 w-4.5" />
              </button>
            </div>

            <div className="space-y-3.5">
              <div className="p-3 rounded-lg bg-slate-50 border border-slate-150 text-[10px] text-slate-700 space-y-2">
                <p className="font-bold text-indigo-600">รายละเอียดบัญชีโอนรับเงินสถาบัน:</p>
                <p>ธนาคารกรุงไทย (ศูนย์บริการวิชาการและแปลภาษา)</p>
                <p className="font-mono font-bold text-slate-850 text-[11px] bg-white px-2 py-1.5 rounded border">
                  เลขบัญชีโอน: 123-4-56789-0
                </p>

                {/* PROMPTPAY QR CODE */}
                <div className="bg-white p-3 rounded-xl border border-slate-200 flex flex-col items-center justify-center shadow-inner mt-2">
                  <div className="w-full bg-[#16305a] text-white py-1 rounded-t-lg text-center font-bold tracking-wider text-[8px] flex items-center justify-center gap-1 shadow-sm">
                    <span className="bg-[#fad135] text-slate-950 px-1 py-0.2 rounded font-black text-[7px]">THAI QR</span>
                    <span>PAYMENT</span>
                  </div>
                  <div className="p-3 bg-white border-x border-b border-slate-200 rounded-b-lg w-full flex justify-center">
                    <svg width="110" height="110" viewBox="0 0 100 100" className="text-slate-900">
                      <rect x="5" y="5" width="22" height="22" fill="none" stroke="currentColor" strokeWidth="4" />
                      <rect x="11" y="11" width="10" height="10" fill="currentColor" />
                      <rect x="73" y="5" width="22" height="22" fill="none" stroke="currentColor" strokeWidth="4" />
                      <rect x="79" y="11" width="10" height="10" fill="currentColor" />
                      <rect x="5" y="73" width="22" height="22" fill="none" stroke="currentColor" strokeWidth="4" />
                      <rect x="11" y="79" width="10" height="10" fill="currentColor" />
                      <rect x="42" y="42" width="16" height="16" fill="currentColor" rx="2" className="text-indigo-600" />
                      <circle cx="50" cy="50" r="4" fill="white" />
                      <rect x="35" y="10" width="6" height="6" fill="currentColor" />
                      <rect x="45" y="5" width="10" height="5" fill="currentColor" />
                      <rect x="60" y="15" width="8" height="8" fill="currentColor" />
                      <rect x="10" y="35" width="8" height="8" fill="currentColor" />
                      <rect x="25" y="45" width="15" height="5" fill="currentColor" />
                      <rect x="45" y="30" width="12" height="12" fill="currentColor" />
                      <rect x="65" y="35" width="6" height="15" fill="currentColor" />
                      <rect x="35" y="65" width="15" height="10" fill="currentColor" />
                      <rect x="55" y="75" width="20" height="12" fill="currentColor" />
                      <rect x="80" y="50" width="15" height="15" fill="currentColor" />
                      <rect x="85" y="75" width="10" height="10" fill="currentColor" />
                      <rect x="35" y="32" width="5" height="5" fill="currentColor" />
                      <rect x="62" y="62" width="10" height="5" fill="currentColor" />
                    </svg>
                  </div>
                  <p className="text-[7.5px] text-slate-500 mt-1.5 text-center font-bold italic">
                    * สามารถสแกนชำระเงินทางแอปธนาคารมือถือได้ทันที
                  </p>
                </div>

                <p className="text-indigo-750 font-black text-xs mt-1.5 bg-indigo-50 p-1.5 rounded text-center border border-indigo-100">
                  จำนวนยอดเงินโอน: {paymentModalData.amount.toLocaleString()} บาท
                </p>
              </div>

              <div>
                <label className="block text-[10px] font-bold text-slate-600 mb-1">
                  กรอกชื่อไฟล์รูปภาพใบสลิป (จำลองการอัปโหลด)
                </label>
                <div className="flex gap-1.5">
                  <input 
                    type="text"
                    placeholder="เช่น slip_transfer_proof.png"
                    value={uploadedSlip || ''}
                    onChange={(e) => setUploadedSlip(e.target.value)}
                    className="flex-grow text-xs p-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                  <button
                    type="button"
                    onClick={() => setUploadedSlip(`slip_cars_husoc_${Math.floor(Math.random() * 80000) + 10000}.png`)}
                    className="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[10px] font-bold px-3 py-1 rounded-xl transition-colors"
                  >
                    สุ่มสลิป
                  </button>
                </div>
              </div>

              <button
                onClick={handleUploadSlip}
                className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-xl text-xs transition-colors shadow-xs"
              >
                ยืนยันการจัดส่งหลักฐานโอนสลิปเข้าระบบ
              </button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL: CERTIFICATE VIEWER */}
      {viewingCertificate && (
        <div className="fixed inset-0 bg-slate-900/70 backdrop-blur-xs flex justify-center items-center p-4 z-50">
          <div className="bg-white p-8 rounded-2xl max-w-lg w-full border-8 text-center relative space-y-4 shadow-2xl animate-in zoom-in-95 duration-200" style={{ borderColor: certTemplate.borderColor }}>
            
            <button 
              onClick={() => setViewingCertificate(null)}
              className="absolute top-2 right-2 p-1 text-slate-400 hover:bg-slate-50 rounded-lg"
            >
              <X className="h-6 w-6" />
            </button>

            <p className="text-xs font-black tracking-widest text-indigo-700 uppercase">{viewingCertificate.title}</p>
            
            {/* LOGO IN CERTIFICATE WATERMARK BACKGROUND */}
            <div className="flex justify-center my-1 opacity-20">
              <img src="logo (1).png" alt="watermark logo" className="h-16 w-16 object-contain" />
            </div>

            <p className="text-[11px] text-slate-500 italic max-w-sm mx-auto">{viewingCertificate.subtitle}</p>
            <h3 className="text-xl font-extrabold text-slate-800 underline my-4">{viewingCertificate.name}</h3>
            <p className="text-xs text-slate-600 leading-relaxed max-w-md mx-auto">{viewingCertificate.detail}</p>
            
            <div className="border-t border-slate-100 pt-4 mt-6 max-w-xs mx-auto text-[10px] text-slate-500">
              <p className="font-bold underline text-slate-700">{viewingCertificate.sign}</p>
              <p>{viewingCertificate.signSub}</p>
              <p className="text-[8px] text-slate-400 mt-2">CARS-HUSOC Certificate System (Verified Hash: {Math.random().toString(36).substring(7).toUpperCase()})</p>
            </div>

            <button
              onClick={() => {
                showToast("ดาวน์โหลดไฟล์ใบตราประกาศ PDF สำเร็จ!");
                setViewingCertificate(null);
              }}
              className="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl text-xs transition-colors"
            >
              ดาวน์โหลด PDF
            </button>
          </div>
        </div>
      )}

      {/* FOOTER */}
      <footer className="bg-slate-900 text-slate-500 py-6 border-t border-slate-850 mt-12 text-center text-[10px] leading-relaxed">
        <div className="max-w-7xl mx-auto px-6 space-y-1">
          <p>© 2026 ศูนย์บริการวิชาการและวิจัย คณะมนุษยศาสตร์และสังคมศาสตร์ (CARS-HUSOC)</p>
          <p>ระบบพอร์ทัลจัดสอบอบรมและบริการแปลภาษาสากลแบบเบ็ดเสร็จ ณ จุดเดียว</p>
        </div>
      </footer>

    </div>
  );
}