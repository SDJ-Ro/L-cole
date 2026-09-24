<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>L'École — Where passions become purpose</title>
<link rel="stylesheet" href="/assets/css/global.css" />
<link rel="stylesheet" href="/assets/css/landing.css" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<main class="l-hero-frame">
  <img class="l-hero-bgimg" alt="Students walking through the L'École campus with the school name visible on the building" src="/assets/images/schoolyard.jpg" />
  <div class="l-hero-scrim" aria-hidden="true"></div>

  <div class="l-hero-content">
    <header class="l-hero-headerbar">
      <div class="wrap l-header">
        <a class="l-header-logo" href="/landing" aria-label="L'École home">
          <span class="l-header-logo-badge"><svg class="icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-graduationCap"/></svg></span>
          <span class="l-header-logo-name">L'École</span>
        </a>
        <nav class="l-header-nav" aria-label="Public navigation">
          <a class="l-header-link" href="#about">About us</a>
          <a class="l-header-link--always" href="/landing/achievements">Honours board</a>
          <a class="l-header-cta" href="#community">Find your access</a>
        </nav>
      </div>
    </header>

    <section class="wrap l-hero-section">
      <div class="l-hero-copy-col">
        <p class="l-hero-eyebrow l-fade-up" style="animation-delay:0s;">
          <svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-sparkles"/></svg>
          Uncover Your Potential
        </p>
        <h1 class="l-hero-title l-fade-up" style="animation-delay:0.06s;">Where passions become purpose.</h1>
        <p class="l-hero-desc l-fade-up" style="animation-delay:0.12s;">Dive into a vibrant ecosystem of extracurriculars, arts, and athletics. At L'École, we believe true growth happens when you explore beyond the classroom.</p>
        <div class="l-hero-actions l-fade-up" style="animation-delay:0.18s;">
          <a class="l-btn-primary" href="#community">
            Join the community
            <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowRight"/></svg>
          </a>
        </div>
      </div>
    </section>

    <section id="community" class="l-community">
      <div class="wrap l-community-inner">
        <div class="l-community-head">
          <div>
            <p class="l-eyebrow">Made for the whole school</p>
            <h2 class="l-h2">Every point of view belongs.</h2>
          </div>
          <p class="l-community-desc">The L'École community stays close to learning without adding noise. Each person sees what helps them participate with clarity, care, and confidence.</p>
        </div>

        <div class="l-pathways">
          <article class="l-pathway-card l-pathway-card--tan" data-reveal>
            <div class="l-pathway-avatar-wrap"><img class="l-pathway-avatar" style="object-position:top;" alt="Student boy and girl in school uniform" src="/assets/images/students.jpg" /></div>
            <div class="l-pathway-body">
              <h3 class="l-pathway-title">Students</h3>
              <p class="l-pathway-copy">Your timetable, grades, and report cards sit beside the clubs, teams, and creative work you take part in. Track achievements and never miss a notice or deadline.</p>
            </div>
          </article>
          <article class="l-pathway-card l-pathway-card--maroon" data-reveal>
            <div class="l-pathway-avatar-wrap"><img class="l-pathway-avatar" style="object-position:top;" alt="Welcoming teacher holding a book" src="/assets/images/teacher.jpg" /></div>
            <div class="l-pathway-body">
              <h3 class="l-pathway-title">Teachers</h3>
              <p class="l-pathway-copy">Plan lessons, record grades, and run the clubs and teams you lead from one place. Log student progress and character notes, and post notices straight to your classes.</p>
            </div>
          </article>
          <article class="l-pathway-card l-pathway-card--lightblue" data-reveal>
            <div class="l-pathway-avatar-wrap"><img class="l-pathway-avatar" style="object-position:top;" alt="Mother and father holding their baby" src="/assets/images/parents.jpg" /></div>
            <div class="l-pathway-body">
              <h3 class="l-pathway-title">Parents</h3>
              <p class="l-pathway-copy">See academic progress as it happens and follow the activities, teams, and achievements shaping your child. Approve school forms and stay in touch with teachers.</p>
            </div>
          </article>
          <article class="l-pathway-card l-pathway-card--terracotta" data-reveal>
            <div class="l-pathway-avatar-wrap"><img class="l-pathway-avatar" style="object-position:top;" alt="School leadership team talking in a campus corridor" src="/assets/images/management.jpg" /></div>
            <div class="l-pathway-body">
              <h3 class="l-pathway-title">Management</h3>
              <p class="l-pathway-copy">Review enrolment and approval queues, oversee staffing, classes, and curriculum, and read school-wide performance at a glance — then publish notices to everyone.</p>
            </div>
          </article>
        </div>

        <div class="l-community-signin">
          <a class="l-btn-pill" href="/auth">
            Sign in
            <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowRight"/></svg>
          </a>
        </div>
      </div>
    </section>

    <section id="achievements" class="ach-section">
      <div class="wrap">
        <div class="ach-head">
          <div>
            <p class="l-eyebrow">Student achievements</p>
            <h2 class="l-h2">Won on the field, stage, and bench.</h2>
          </div>
          <p class="l-community-desc">Every result is recorded with the same detail our teachers submit — tournament, organiser, venue, level, and the students who were there. Here are a few from the last two seasons.</p>
        </div>

        <div class="ach-list">
          <article class="ach-row" data-reveal>
            <div class="ach-row-media"><img alt="School football squad celebrating with a trophy on a floodlit pitch" src="/assets/images/football.jpg" /></div>
            <div class="ach-row-body">
              <div class="ach-tags">
                <span class="ach-tag" style="background:var(--maroon);color:var(--alabaster);">Football</span>
                <span class="ach-tag ach-tag--neutral">Provincial</span>
                <span class="ach-tag ach-tag--neutral">2023</span>
              </div>
              <h3 class="ach-title">Div-1 League Runners Up</h3>
              <p class="ach-meta">
                <span><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-calendarDays"/></svg> Nov 18, 2023</span>
                <span><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-mapPin"/></svg> SSC Grounds, Colombo</span>
                <span><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-trophy"/></svg> Silver Medal</span>
              </p>
              <p class="ach-summary">After an undefeated group stage, the senior squad battled through a tense semi-final on penalties to reach the provincial final. Despite a spirited comeback, the team finished as runners up — the club's best league result in over a decade.</p>
            </div>
          </article>

          <article class="ach-row ach-row--reversed" data-reveal>
            <div class="ach-row-media"><img alt="School athlete crossing the finish line on a running track" src="/assets/images/running.jpg" /></div>
            <div class="ach-row-body">
              <div class="ach-tags">
                <span class="ach-tag" style="background:var(--sunshine);color:var(--midnight);">Athletics</span>
                <span class="ach-tag ach-tag--neutral">All-Island</span>
                <span class="ach-tag ach-tag--neutral">2024</span>
              </div>
              <h3 class="ach-title">400m All-Island Gold</h3>
              <p class="ach-meta">
                <span><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-calendarDays"/></svg> Aug 05, 2024</span>
                <span><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-mapPin"/></svg> Sugathadasa Stadium, Colombo</span>
                <span><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-trophy"/></svg> Gold Medal</span>
              </p>
              <p class="ach-summary">Nethmi took the 400m final from lane six, holding the lead through the last bend and crossing in a new personal best. It is the school's first individual track gold at all-island level in four seasons.</p>
            </div>
          </article>

          <article class="ach-row" data-reveal>
            <div class="ach-row-media"><img alt="Two students speaking at a debating podium in a panelled hall" src="/assets/images/debate.jpg" /></div>
            <div class="ach-row-body">
              <div class="ach-tags">
                <span class="ach-tag" style="background:var(--lightblue);color:var(--midnight);">Debating</span>
                <span class="ach-tag ach-tag--neutral">National</span>
                <span class="ach-tag ach-tag--neutral">2024</span>
              </div>
              <h3 class="ach-title">National Debate Champions</h3>
              <p class="ach-meta">
                <span><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-calendarDays"/></svg> Mar 22, 2024</span>
                <span><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-mapPin"/></svg> BMICH, Colombo</span>
                <span><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-trophy"/></svg> Gold Medal</span>
              </p>
              <p class="ach-summary">Four rounds, one semi-final, and a unanimous decision in the grand final on education policy. The senior team argued opposition and took the title along with the Best Speaker award.</p>
            </div>
          </article>
        </div>

        <div class="ach-seeall">
          <a class="l-btn-pill" href="/landing/achievements">
            See all achievements
            <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowRight"/></svg>
          </a>
        </div>
      </div>
    </section>

    <section id="about" class="l-about">
      <div class="wrap">
        <div class="l-about-head">
          <p class="l-eyebrow">Our System</p>
          <h2 class="l-h2">Focus on growth, we handle the rest.</h2>
          <p class="l-about-desc">L'École is built around the idea that extracurriculars are just as important as academics for student growth. Our platform seamlessly integrates both, providing a complete picture of every student's journey.</p>
        </div>

        <div class="l-features">
          <div class="l-feature-card">
            <span class="l-feature-icon l-feature-icon--lightblue"><svg class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-trendingUp"/></svg></span>
            <h3 class="l-feature-title">Extracurricular Growth</h3>
            <p class="l-feature-copy">Manage clubs, teams, and creative projects alongside academics. We provide the tools to track participation, achievements, and personal development outside the classroom.</p>
          </div>
          <div class="l-feature-card">
            <span class="l-feature-icon l-feature-icon--tan"><svg class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-shieldCheck"/></svg></span>
            <h3 class="l-feature-title">Character Certificates</h3>
            <p class="l-feature-copy">Automatically generate certificates based on student achievements and participation. Recognize and reward the soft skills and character traits that matter most.</p>
          </div>
          <div class="l-feature-card l-feature-card--wide">
            <span class="l-feature-icon l-feature-icon--sunshine"><svg class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-bookOpen"/></svg></span>
            <h3 class="l-feature-title">Academic Functions</h3>
            <p class="l-feature-copy">Seamlessly handle report cards, grading, and curriculum planning. Our system ensures that academic progress is tracked efficiently, giving teachers more time to teach.</p>
          </div>
        </div>
      </div>
    </section>

    <footer class="l-footer">
      <div class="wrap l-footer-grid">
        <div>
          <div class="l-footer-brand">
            <span class="l-footer-brand-badge"><svg class="icon" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-graduationCap"/></svg></span>
            <span class="l-footer-brand-name">L'École</span>
          </div>
          <p class="l-footer-tagline">A school community where academic focus meets the joy of making, performing, competing, contributing, and growing.</p>
        </div>
        <div class="l-footer-col">
          <p class="l-footer-col-label">School</p>
          <p class="l-footer-line">123 Education Lane<br />Knowledge City, KC 10000</p>
          <a href="mailto:hello@lecole.edu">hello@lecole.edu</a>
          <a href="tel:+1234567890">+1 (234) 567-890</a>
        </div>
        <div class="l-footer-col">
          <p class="l-footer-col-label">Platforms</p>
          <a href="/auth/student">Student Portal</a>
          <a href="/auth/teacher">Teacher Portal</a>
          <a href="/auth/parent">Parent Portal</a>
          <a href="/auth/management">Management Portal</a>
          <a href="/auth/admin">Admin Portal</a>
        </div>
        <div class="l-footer-col">
          <p class="l-footer-col-label">About</p>
          <a href="#about">Our System</a>
          <a href="#about">Extracurriculars</a>
          <a href="#about">Academics</a>
        </div>
      </div>
      <div class="l-footer-bottom">
        <div class="wrap l-footer-bottom-inner">
          <p>© 2026 L'École</p>
          <p>Curious minds. Generous hearts. Brave futures.</p>
        </div>
      </div>
    </footer>
  </div>
</main>

<script src="/assets/js/reveal.js"></script>
</body>
</html>
