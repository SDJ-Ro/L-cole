<?php
/**
 * =========================================================================
 * L'ÉCOLE — PARENT APPROVALS MODEL
 * =========================================================================
 * School sends consent/permission requests to the parent. The parent
 * approves or declines them here (field trips, activity enrolments,
 * medical consent, extracurricular programme participation, etc.)
 * =========================================================================
 */
class ParentApprovalModel {

    protected static $items = [
        // ---- PENDING — awaiting parent decision --------------------------
        [
            'id'          => 'pa-001',
            'type'        => 'Extracurriculars',
            'title'       => 'Under-15 Swimming Club Enrolment',
            'tag'         => 'Join Request',
            'color'       => 'terracotta',
            'meta'        => 'Coach: Mr. Niroshan Perera · Thursdays 15:00–17:00',
            'description' => 'The Sports Department requests your consent to enrol Nethmi Perera in the Under-15 Swimming Club for the upcoming inter-school season. Three weekly sessions at the school pool.',
            'author'      => 'Sports Department',
            'date'        => 'Sep 18, 2026',
            'status'      => 'Pending',
            'feedback'    => '',
        ],
        [
            'id'          => 'pa-002',
            'type'        => 'Extracurriculars',
            'title'       => 'Drama & Theatre Society Participation',
            'tag'         => 'Join Request',
            'color'       => 'terracotta',
            'meta'        => 'TIC: Ms. Kanchana Jayasuriya · Fridays 14:30–16:30',
            'description' => 'The Arts Faculty invites Nethmi to join the Drama Society for the inter-school theatrical production in November. Parental consent is required for evening dress rehearsals.',
            'author'      => 'Arts Faculty',
            'date'        => 'Sep 15, 2026',
            'status'      => 'Pending',
            'feedback'    => '',
        ],
        [
            'id'          => 'pa-003',
            'type'        => 'Extracurriculars',
            'title'       => 'Annual Science Fair Field Trip',
            'tag'         => 'Field Trip',
            'color'       => 'terracotta',
            'meta'        => 'Destination: National Science Centre, Colombo · Oct 04, 2026',
            'description' => 'Permission requested for Nethmi to attend the National Science Fair field trip on October 4th. Departure 07:30, return 17:00. Supervised by three science teachers.',
            'author'      => 'Science Department',
            'date'        => 'Sep 10, 2026',
            'status'      => 'Pending',
            'feedback'    => '',
        ],

        // ---- APPROVED — parent consented --------------------------------
        [
            'id'          => 'pa-004',
            'type'        => 'Extracurriculars',
            'title'       => 'School Debating Team Selection',
            'tag'         => 'Join Request',
            'color'       => 'terracotta',
            'meta'        => 'TIC: Mr. Asanka Fernando · Wednesdays 15:00–16:30',
            'description' => 'Nethmi has been selected for the School Debating Team. Parent approval confirmed for participation in inter-school rounds.',
            'author'      => 'English Department',
            'date'        => 'Aug 22, 2026',
            'status'      => 'Approved',
            'feedback'    => '',
        ],
        [
            'id'          => 'pa-005',
            'type'        => 'Extracurriculars',
            'title'       => 'Environmental Science Club Membership',
            'tag'         => 'Join Request',
            'color'       => 'terracotta',
            'meta'        => 'TIC: Mrs. Dilani Ratnayake · Tuesdays 15:00–16:00',
            'description' => 'Consent granted for Nethmi to join the Environmental Science Club and participate in the conservation project for the science fair.',
            'author'      => 'Science Department',
            'date'        => 'Aug 10, 2026',
            'status'      => 'Approved',
            'feedback'    => '',
        ],
        [
            'id'          => 'pa-006',
            'type'        => 'Extracurriculars',
            'title'       => 'Term 2 Educational Trip — Botanical Gardens',
            'tag'         => 'Field Trip',
            'color'       => 'terracotta',
            'meta'        => 'Destination: Royal Botanical Gardens, Peradeniya · Jul 18, 2026',
            'description' => 'Parent consent confirmed for Nethmi to attend the Grade 6 educational trip to the Botanical Gardens for the Biology curriculum.',
            'author'      => 'Biology Department',
            'date'        => 'Jul 10, 2026',
            'status'      => 'Approved',
            'feedback'    => '',
        ],

        // ---- REJECTED — parent declined ---------------------------------
        [
            'id'          => 'pa-007',
            'type'        => 'Extracurriculars',
            'title'       => 'Under-17 Cricket Squad Invitation',
            'tag'         => 'Join Request',
            'color'       => 'terracotta',
            'meta'        => 'Coach: Mr. Kaluarachchi · Mon & Wed 15:30–17:30',
            'description' => 'The Sports Department invited Nethmi to trial for the Under-17 Cricket Squad. Parent declined participation.',
            'author'      => 'Sports Department',
            'date'        => 'Jul 12, 2026',
            'status'      => 'Rejected',
            'feedback'    => 'Nethmi has an existing commitment with her external coaching programme on Monday and Wednesday afternoons. We appreciate the invitation and may reconsider next term.',
        ],
        [
            'id'          => 'pa-008',
            'type'        => 'Extracurriculars',
            'title'       => 'Saturday Morning Study Sessions',
            'tag'         => 'Programme',
            'color'       => 'terracotta',
            'meta'        => 'Coordinator: Mrs. Irangani Silva · Saturdays 08:00–11:00',
            'description' => 'The Academic Department invited Nethmi to join the optional Saturday morning revision programme for Grade 6 students.',
            'author'      => 'Academic Department',
            'date'        => 'Jun 30, 2026',
            'status'      => 'Rejected',
            'feedback'    => 'We have family commitments on Saturday mornings and prefer to manage revision at home. Thank you for the offer.',
        ],
    ];

    /**
     * Returns all approval items (all statuses).
     */
    public static function getAll(): array {
        return self::$items;
    }

    /**
     * Returns counts keyed by status: Pending, Approved, Rejected.
     */
    public static function getStatusCounts(): array {
        $counts = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0];
        foreach (self::$items as $item) {
            $s = $item['status'] ?? 'Pending';
            if (isset($counts[$s])) {
                $counts[$s]++;
            }
        }
        return $counts;
    }
}

if (!class_exists('App\\Models\\ParentApprovalModel', false)) {
    class_alias('ParentApprovalModel', 'App\\Models\\ParentApprovalModel');
}
