<x-admin::layouts>
    <x-slot:title>
        Priya Nair
    </x-slot>

    @php
        $timeline = [
            ['icon' => 'fa-solid fa-user-plus', 'class' => 'contact-detail-timeline__icon--green', 'title' => 'Assigned to Ojasvi Dutta', 'meta' => 'Auto - India territory', 'date' => 'Jan 2025'],
            ['icon' => 'fa-solid fa-phone', 'class' => 'contact-detail-timeline__icon--blue', 'title' => 'Logged call', 'detail' => 'discussed subscription', 'meta' => '15 min', 'date' => 'Mar 2025'],
            ['icon' => 'fa-solid fa-cart-shopping', 'class' => 'contact-detail-timeline__icon--green', 'title' => 'Purchased Education Subscription', 'meta' => 'Annual plan - via portal checkout', 'date' => '2d ago'],
            ['icon' => 'fa-regular fa-circle-check', 'class' => 'contact-detail-timeline__icon--purple', 'title' => 'Completed', 'detail' => 'Lesson 12 — Cell-cell communication ★★★★★', 'meta' => 'Single-Cell RNA-Seq', 'quote' => 'Loved the CellChat walkthrough.', 'date' => '5d ago'],
            ['icon' => 'fa-regular fa-circle-check', 'class' => 'contact-detail-timeline__icon--purple', 'title' => 'Completed', 'detail' => 'Lesson 8 — Pseudotime with Monocle ★★★★★', 'meta' => 'Single-Cell RNA-Seq', 'date' => '2w ago'],
            ['icon' => 'fa-solid fa-arrow-up', 'class' => 'contact-detail-timeline__icon--green', 'title' => 'Stage changed', 'detail' => 'Engaged → Customer', 'meta' => 'Auto on first purchase', 'date' => '2w ago'],
            ['icon' => 'fa-solid fa-clipboard-list', 'class' => 'contact-detail-timeline__icon--blue', 'title' => 'Registered for scRNA-Seq workshop', 'meta' => 'Google Form - referrer: learn.omicslogic.com', 'date' => 'Jan 2025'],
            ['icon' => 'fa-regular fa-note-sticky', 'class' => 'contact-detail-timeline__icon--slate', 'title' => 'Note by Ojasvi', 'detail' => 'strong candidate for TA role', 'meta' => 'Internal', 'date' => 'Dec 2024'],
        ];
    @endphp

    <section class="contact-detail-page">
        <a
            href="{{ route('admin.contacts.persons.index') }}"
            class="contact-detail-back"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Back to contacts
        </a>

        <section class="contact-detail-hero">
            <div class="contact-detail-hero__left">
                <span
                    class="contact-detail-avatar"
                    data-contact-avatar
                >PN</span>

                <div>
                    <div  class="contact-detail-hero__title" data-contact-field="name">Priya Nair</div>

                    <div class="contact-detail-meta">
                        <span><i class="fa-solid fa-briefcase"></i> <span data-contact-field="title">PhD Scholar, Computational Biology</span></span>
                        <span><i class="fa-solid fa-building-columns"></i> <span data-contact-field="organization">IIT Jodhpur</span></span>
                        <span><i class="fa-solid fa-location-dot"></i> <span data-contact-field="country">India</span></span>
                        <span
                            class="contact-detail-pill contact-detail-pill--green"
                            data-contact-field="stage"
                        >Customer</span>
                        <span>Owner: <strong class="contact-detail-owner-dot">OD</strong> Ojasvi Dutta</span>
                    </div>

                    <div class="contact-detail-tags">
                        <span data-contact-field="stage">Customer</span>
                        <span>PGDP alumnus</span>
                        <span data-contact-field="program">scRNA-seq</span>
                    </div>
                </div>
            </div>

            <div class="contact-detail-score">
                <span>Lead score <i class="fa-regular fa-circle-question"></i></span>
                <strong>92</strong>

                <div class="contact-detail-score__actions">
                    <button type="button"><i class="fa-solid fa-user-plus"></i> Reassign</button>
                    <a
                        href="{{ route('admin.contacts.persons.edit_profile', 1) }}"
                        class="contact-detail-btn"
                    >
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                    <button
                        type="button"
                        class="contact-detail-delete"
                        onclick="confirmDetailContactDelete('Priya Nair')"
                    >
                        <i class="fa-regular fa-trash-can"></i> Delete
                    </button>
                </div>
            </div>
        </section>

        <div class="contact-detail-actions">
            <button type="button"><i class="fa-solid fa-phone"></i> Log call</button>
            <button type="button"><i class="fa-solid fa-envelope"></i> Log email</button>
            <button type="button"><i class="fa-regular fa-square-check"></i> Add task</button>
            <button type="button"><i class="fa-regular fa-note-sticky"></i> Add note</button>
        </div>

        <section class="contact-detail-grid">
            <div class="contact-detail-left">
                <article class="contact-detail-card">
                    <div class="contact-detail-card__head">
                        <h3>Sales</h3>
                        <a
                            href="{{ route('admin.contacts.persons.edit_profile', 1) }}"
                            class="contact-detail-btn"
                        >
                            <i class="fa-regular fa-pen-to-square"></i> Edit
                        </a>
                    </div>

                    <div class="contact-detail-kv">
                        <span>Owner</span>
                        <strong><span class="contact-detail-owner-dot">OD</span> Ojasvi Dutta</strong>
                    </div>

                    <div class="contact-detail-status">
                        <span>Lead status</span>
                        <div>
                            <b>New</b>
                            <b>Contacted</b>
                            <b>Qualified</b>
                            <b>Proposal</b>
                            <b class="is-won">Won</b>
                        </div>
                    </div>

                    <div class="contact-detail-kv"><span>Next action</span><strong>Annual renewal check-in</strong></div>
                    <div class="contact-detail-kv"><span>Due</span><strong>Jun 20</strong></div>
                    <div class="contact-detail-kv"><span>Last contacted</span><strong>2d ago</strong></div>
                </article>

                <article class="contact-detail-card">
                    <div class="contact-detail-card__head">
                        <h2>Tasks & follow-ups</h2>
                        <span>1 open</span>
                    </div>

                    <div class="contact-detail-task is-done">
                        <span></span>
                        <del>Send renewal quote</del>
                        <strong>done</strong>
                    </div>

                    <div class="contact-detail-task">
                        <span></span>
                        <p>Confirm interest in TA role</p>
                        <strong>due Jun 22</strong>
                    </div>

                    <button
                        type="button"
                        class="contact-detail-add-task"
                    >
                        <i class="fa-solid fa-plus"></i> Add task
                    </button>
                </article>

                <article class="contact-detail-card">
                    <div class="contact-detail-card__head">
                        <h3>Profile</h3>
                        <a
                            href="{{ route('admin.contacts.persons.edit_profile', 1) }}"
                            class="contact-detail-btn"
                        >
                            <i class="fa-regular fa-pen-to-square"></i> Edit
                        </a>
                    </div>

                    <div class="contact-detail-kv"><span>Primary email</span><a href="mailto:priya.nair@iitj.ac.in" data-contact-email>priya.nair@iitj.ac.in</a></div>
                    <div class="contact-detail-kv"><span>Phone</span><strong data-contact-field="phone">+91 98XXX 41122</strong></div>
                    <div class="contact-detail-kv"><span>Education</span><strong data-contact-field="education">PhD</strong></div>
                    <div class="contact-detail-kv"><span>Research interest</span><strong data-contact-field="research">Single-cell, Transcriptomics</strong></div>
                    <div class="contact-detail-kv"><span>Organization</span><a href="#" data-contact-field="organization">IIT Jodhpur</a></div>
                    <div class="contact-detail-kv"><span>First seen</span><strong>Mar 2023</strong></div>
                    <div class="contact-detail-kv"><span>Original source</span><strong>Google Form — PGDP 2023</strong></div>
                </article>

                <article class="contact-detail-card contact-detail-identities">
                    <div class="contact-detail-card__head">
                        <strong>Identities</strong>
                        <span>3</span>
                    </div>

                    <div class="contact-detail-identity contact-detail-identity--primary">
                        <div data-contact-email>priya.nair@iitj.ac.in</div>
                        <span>primary</span>
                    </div>

                    <div class="contact-detail-identity">
                        <i class="fa-regular fa-envelope"></i>
                        <div>priyanair.bio@gmail.com</div>
                    </div>

                    <div class="contact-detail-identity contact-detail-identity--phone">
                        <i class="fa-solid fa-phone"></i>
                        <div data-contact-field="phone">+91 98XXX 41122</div>
                    </div>

                    <div class="contact-detail-deduped">
                        <strong>Deduplicated.</strong>
                        2 records merged: a 2023 PGDP form (gmail) + 2024 portal account (institute email). Unified Jan 2025.
                    </div>
                </article>

                <article class="contact-detail-card contact-detail-enrollments">
                    <div class="contact-detail-card__head">
                        <h3><i class="fa-solid fa-graduation-cap"></i> Portal enrollments</h3>
                    </div>

                    <div class="contact-detail-enrollment">
                        <div>
                            <span>Single-Cell RNA-Seq — Trajectory Analysis</span>
                            <span>88%</span>
                        </div>

                        <p>Education Subscription · 14/16 lessons</p>

                        <div class="contact-detail-progress">
                            <span class="contact-detail-progress__bar--88"></span>
                        </div>
                    </div>

                    <div class="contact-detail-enrollment">
                        <div>
                            <span>RNA-Seq Data Analysis</span>
                            <span>100%</span>
                        </div>

                        <p>Completed · certificate issued</p>

                        <div class="contact-detail-progress">
                            <span class="contact-detail-progress__bar--100"></span>
                        </div>
                    </div>
                </article>
            </div>

            <div class="contact-detail-right">
                <article class="contact-detail-card contact-detail-timeline-card">
                    <div class="contact-detail-card__head">
                        <h3>Activity timeline</h3>
                        <span>8 events</span>
                    </div>

                    <div class="contact-detail-timeline">
                        @foreach ($timeline as $event)
                            <div class="contact-detail-timeline__item">
                                <span class="contact-detail-timeline__icon {{ $event['class'] }}">
                                    <i class="{{ $event['icon'] }}"></i>
                                </span>

                                <div>
                                    <p>
                                        <strong>{{ $event['title'] }}</strong>

                                        @isset($event['detail'])
                                            <span>{{ $event['detail'] }}</span>
                                        @endisset
                                    </p>

                                    <small>{{ $event['meta'] }}</small>

                                    @isset($event['quote'])
                                        <blockquote>{{ $event['quote'] }}</blockquote>
                                    @endisset
                                </div>

                                <time>{{ $event['date'] }}</time>
                            </div>
                        @endforeach
                    </div>
                </article>

                <!-- <div class="contact-detail-scenario">
                    <strong>Real scenario.</strong>
                    The rep who owns this lead opens one page and sees the full story: marketing's record of every form and lesson, plus their own sales thread &mdash; calls logged, the lead-status pipeline, the next task and its due date. The handoff from marketing to sales happens without anything falling through the cracks.
                </div> -->
            </div>
        </section>
    </section>

    @pushOnce('scripts')
        <script>
            window.contactProfileDefaults = {
                name: 'Priya Nair',
                title: 'PhD Scholar, Computational Biology',
                organization: 'IIT Jodhpur',
                country: 'India',
                stage: 'Customer',
                education: 'PhD',
                email: 'priya.nair@iitj.ac.in',
                phone: '+91 98XXX 41122',
                research: 'Single-cell, Transcriptomics',
                program: 'Single-Cell RNA-Seq',
            };

            window.getContactProfile = function () {
                try {
                    return {
                        ...window.contactProfileDefaults,
                        ...JSON.parse(localStorage.getItem('crm.contact.1') || '{}'),
                    };
                } catch (error) {
                    return window.contactProfileDefaults;
                }
            };

            window.applyContactProfile = function () {
                const profile = window.getContactProfile();

                Object.entries(profile).forEach(([key, value]) => {
                    document.querySelectorAll(`[data-contact-field="${key}"]`).forEach((element) => {
                        element.textContent = value;
                    });
                });

                document.querySelectorAll('[data-contact-email]').forEach((element) => {
                    element.textContent = profile.email;
                    element.href = `mailto:${profile.email}`;
                });

                const avatar = document.querySelector('[data-contact-avatar]');

                if (avatar) {
                    avatar.textContent = profile.name
                        .split(' ')
                        .filter(Boolean)
                        .slice(0, 2)
                        .map((part) => part[0])
                        .join('')
                        .toUpperCase();
                }
            };

            window.confirmDetailContactDelete = function (contactName) {
                const afterDelete = function () {
                    localStorage.removeItem('crm.contact.1');

                    window.emitter?.emit('add-flash', {
                        type: 'success',
                        message: `${contactName} deleted successfully.`,
                    });

                    window.location.href = "{{ route('admin.contacts.persons.index') }}";
                };

                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Delete contact?',
                        text: `Are you sure you want to delete ${contactName}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'Delete',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            afterDelete();
                        }
                    });

                    return;
                }

                if (window.emitter) {
                    window.emitter.emit('open-confirm-modal', {
                        title: 'Delete contact?',
                        message: `Are you sure you want to delete ${contactName}?`,
                        options: {
                            btnDisagree: 'Cancel',
                            btnAgree: 'Delete',
                        },
                        agree: afterDelete,
                    });

                    return;
                }

                if (window.confirm(`Are you sure you want to delete ${contactName}?`)) {
                    afterDelete();
                }
            };

            window.applyContactProfile();
        </script>
    @endPushOnce
</x-admin::layouts>
