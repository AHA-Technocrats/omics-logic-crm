<x-admin::layouts>
    <x-slot:title>
        Edit contact — Priya Nair
    </x-slot>

    <section class="contact-edit-page">
        <a
            href="{{ route('admin.contacts.persons.detail', 1) }}"
            class="contact-edit-back"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Cancel & go back
        </a>

        <form
            id="contact-edit-form"
            class="contact-edit-card"
            onsubmit="saveContactProfile(event)"
        >
            <div class="contact-edit-card__head">
                <i class="fa-regular fa-pen-to-square"></i>
                Edit contact — <span data-edit-title>Priya Nair</span>
                </div>

            <div class="contact-edit-grid">
                <label>
                    <span>Full name</span>
                    <input
                        name="name"
                        type="text"
                        value="Priya Nair"
                    >
                </label>                
                <label>
                    <span>Title / role</span>
                    <input
                        name="title"
                        type="text"
                        value="PhD Scholar, Computational Biology"
                    >
                </label>

                <label>
                    <span>Organization</span>
                    <input
                        name="organization"
                        type="text"
                        value="IIT Jodhpur"
                    >
                </label>

                <label>
                    <span>Country</span>
                    <select name="country">
                        <option>India</option>
                        <option>United States</option>
                        <option>Nigeria</option>
                        <option>Canada</option>
                    </select>
                </label>

                <label>
                    <span>Lifecycle stage</span>
                    <select name="stage">
                        <option>New</option>
                        <option>Engaged</option>
                        <option>Customer</option>
                        <option>Dormant</option>
                    </select>
                </label>

                <label>
                    <span>Education</span>
                    <select name="education">
                        <option>Masters</option>
                        <option>PhD</option>
                        <option>Faculty</option>
                        <option>Industry</option>
                    </select>
                </label>

                <label>
                    <span>Primary email</span>
                    <input
                        name="email"
                        type="email"
                        value="priya.nair@iitj.ac.in"
                    >
                </label>

                <label>
                    <span>Phone</span>
                    <input
                        name="phone"
                        type="text"
                        value="+91 98XXX 41122"
                    >
                </label>

                <label>
                    <span>Research interest</span>
                    <input
                        name="research"
                        type="text"
                        value="Single-cell, Transcriptomics"
                    >
                </label>

                <label>
                    <span>Program / interest</span>
                    <input
                        name="program"
                        type="text"
                        value="Single-Cell RNA-Seq"
                    >
                </label>
            </div>

            <div class="contact-edit-actions">
                <a href="{{ route('admin.contacts.persons.detail', 1) }}">
                    <i class="fa-solid fa-xmark"></i>
                    Cancel
                </a>

                <button type="submit">
                    <i class="fa-solid fa-check"></i>
                    Save changes
                </button>
            </div>

            <p class="contact-edit-note">
                <i class="fa-regular fa-clock"></i>
                In the live system every edit is written to the Audit log so changes are traceable and reversible.
            </p>
        </form>
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

            window.fillContactEditForm = function () {
                const profile = window.getContactProfile();
                const form = document.getElementById('contact-edit-form');

                Object.entries(profile).forEach(([key, value]) => {
                    if (form?.elements[key]) {
                        form.elements[key].value = value;
                    }
                });

                document.querySelector('[data-edit-title]').textContent = profile.name;
            };

            window.saveContactProfile = function (event) {
                event.preventDefault();

                const form = event.currentTarget;
                const profile = {
                    name: form.elements.name.value.trim(),
                    title: form.elements.title.value.trim(),
                    organization: form.elements.organization.value.trim(),
                    country: form.elements.country.value,
                    stage: form.elements.stage.value,
                    education: form.elements.education.value,
                    email: form.elements.email.value.trim(),
                    phone: form.elements.phone.value.trim(),
                    research: form.elements.research.value.trim(),
                    program: form.elements.program.value.trim(),
                };

                localStorage.setItem('crm.contact.1', JSON.stringify(profile));

                window.emitter?.emit('add-flash', {
                    type: 'success',
                    message: 'Contact changes saved successfully.',
                });

                window.location.href = "{{ route('admin.contacts.persons.detail', 1) }}";
            };

            window.fillContactEditForm();
        </script>
    @endPushOnce
</x-admin::layouts>
