<?php
/**
 * =========================================================================
 * L'ÉCOLE — FEEDBACK DATA MODEL
 * =========================================================================
 * Central data source for Parent and Teacher feedback records.
 * Provides unified schema for both portals.
 */
class FeedbackModel {

    /**
     * Feedback records for parents viewing feedback about their child (e.g. Nethmi Perera).
     */
    public static function getForParent($childId = 1) {
        return [
            [
                'id'          => 1,
                'type'        => 'Positive',
                'subject'     => 'Mathematics',
                'teacher'     => 'Mr. Fernando',
                'teacherRole' => 'Mathematics Teacher',
                'student'     => 'Nethmi Perera',
                'date'        => 'Oct 25, 2024',
                'preview'     => 'Nethmi has shown remarkable improvement in algebra this term...',
                'fullText'    => 'Nethmi has shown remarkable improvement in algebra this term. She actively participates in class discussions and consistently helps her peers during group exercises. Her dedication to mastering complex concepts is truly commendable.'
            ],
            [
                'id'          => 2,
                'type'        => 'Constructive',
                'subject'     => 'English Literature',
                'teacher'     => 'Ms. Perera',
                'teacherRole' => 'English Teacher',
                'student'     => 'Nethmi Perera',
                'date'        => 'Oct 18, 2024',
                'preview'     => 'Great analytical skills, but needs to focus more on essay structure...',
                'fullText'    => 'Nethmi possesses great analytical skills when breaking down poetry. However, she needs to focus more on her essay structure and time management during written assessments. I recommend she practices outlining her thoughts before writing.'
            ],
            [
                'id'          => 3,
                'type'        => 'Positive',
                'subject'     => 'Science',
                'teacher'     => 'Mrs. Ratnayake',
                'teacherRole' => 'Science Teacher',
                'student'     => 'Nethmi Perera',
                'date'        => 'Sep 30, 2024',
                'preview'     => 'Outstanding project presentation on renewable energy...',
                'fullText'    => 'Outstanding project presentation on renewable energy! Nethmi went above and beyond the syllabus requirements, demonstrating a deep understanding of the subject matter and excellent presentation skills.'
            ],
            [
                'id'          => 4,
                'type'        => 'Negative',
                'subject'     => 'Discipline & Punctuality',
                'teacher'     => 'Mr. Wickramasinghe',
                'teacherRole' => 'Sectional Head',
                'student'     => 'Nethmi Perera',
                'date'        => 'Sep 22, 2024',
                'preview'     => 'Frequent delays in submitting practical lab records and morning late arrival...',
                'fullText'    => 'Nethmi was recorded as arriving late on three consecutive Monday mornings, and her Chemistry lab journal submission was delayed by a full week without prior notice. Please ensure stricter adherence to morning attendance and submission deadlines.'
            ],
            [
                'id'          => 5,
                'type'        => 'Constructive',
                'subject'     => 'History',
                'teacher'     => 'Mr. Silva',
                'teacherRole' => 'History Teacher',
                'student'     => 'Nethmi Perera',
                'date'        => 'Sep 15, 2024',
                'preview'     => 'Needs to participate more in class discussions...',
                'fullText'    => 'Nethmi has a good grasp of historical facts, but she is often hesitant to share her thoughts during class discussions. Encouraging her to speak up will help build her confidence and improve her overall engagement.'
            ],
            [
                'id'          => 6,
                'type'        => 'Positive',
                'subject'     => 'Art & Design',
                'teacher'     => 'Ms. Jayasinghe',
                'teacherRole' => 'Art Teacher',
                'student'     => 'Nethmi Perera',
                'date'        => 'Sep 05, 2024',
                'preview'     => 'Exceptional creativity in the recent watercolor project...',
                'fullText'    => 'Exceptional creativity in the recent watercolor project! Nethmi has a great eye for color blending and composition. Her artwork was selected to be displayed in the main exhibition hallway.'
            ]
        ];
    }

    /**
     * Feedback records for teachers in their feedback channel.
     */
    public static function getForTeacher($teacherId = 1) {
        return [
            [
                'id'          => 101,
                'type'        => 'Positive',
                'subject'     => 'Mathematics Term 1 Performance Inquiry',
                'teacher'     => 'Mr. Fernando',
                'teacherRole' => 'Mathematics Teacher',
                'student'     => 'Nethmi Perera',
                'studentId'   => '2021/0456',
                'parent'      => 'Suresh Perera',
                'date'        => 'Mar 12, 2026',
                'preview'     => 'Inquiring about Nethmi\'s progress in Mathematics Term 1. She has been practicing daily at home...',
                'fullText'    => 'Dear teacher, I would like to inquire about Nethmi\'s progress in Mathematics Term 1. She has been practicing daily at home and has developed a keen interest in algebra problems. We appreciate the extra worksheets provided.'
            ],
            [
                'id'          => 102,
                'type'        => 'Negative',
                'subject'     => 'Regarding Term 1 Exam Schedule & Revision',
                'teacher'     => 'Mr. Fernando',
                'teacherRole' => 'Mathematics Teacher',
                'student'     => 'Amara Silva',
                'studentId'   => '2021/0203',
                'parent'      => 'Kavinda Silva',
                'date'        => 'Mar 10, 2026',
                'preview'     => 'Discrepancy observed in the revision material dates causing severe study schedule clashes...',
                'fullText'    => 'Could you please clarify the severe discrepancy in the revision timetable sent home last Friday? Students were given overlapping revision periods with insufficient lead time before the assessment.'
            ],
            [
                'id'          => 103,
                'type'        => 'Positive',
                'subject'     => 'Science Project Materials Submission',
                'teacher'     => 'Mrs. Ratnayake',
                'teacherRole' => 'Science Teacher',
                'student'     => 'Maya Kapoor',
                'studentId'   => '2022/0118',
                'parent'      => 'Mrs. Kapoor',
                'date'        => 'Mar 08, 2026',
                'preview'     => 'Submitting the required documentation and materials for the upcoming Science project assessment...',
                'fullText'    => 'Submitting the required documentation and materials for Maya\'s upcoming Science project assessment on renewable solar cells. All safety protocols have been reviewed and documented.'
            ],
            [
                'id'          => 104,
                'type'        => 'Constructive',
                'subject'     => 'Extra Curricular Activities Participation',
                'teacher'     => 'Mr. Perera',
                'teacherRole' => 'Physical Education Head',
                'student'     => 'John Doe',
                'studentId'   => '2021/0892',
                'parent'      => 'Mark Doe (Guardian)',
                'date'        => 'Mar 05, 2026',
                'preview'     => 'Inquiring about John\'s schedule for upcoming sports and club activities...',
                'fullText'    => 'Inquiring about John\'s schedule for upcoming athletics and swimming club activities. He wants to balance his academic studies alongside athletics meets and requests practical advice.'
            ]
        ];
    }

    /**
     * Categories / Types list.
     */
    public static function getTypes() {
        return ['All Types', 'Positive', 'Constructive', 'Negative'];
    }
}

if (!class_exists('App\\Models\\FeedbackModel', false)) {
    class_alias('FeedbackModel', 'App\\Models\\FeedbackModel');
}
