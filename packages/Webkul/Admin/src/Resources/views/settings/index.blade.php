<x-admin::layouts>
    <x-slot:title>
        Settings
    </x-slot>

    @php
        $generalSettings = [
            ['label' => 'Organization', 'value' => 'OmicsLogic', 'type' => 'text'],
            ['label' => 'CRM domain', 'value' => 'crm.omicslogic.com', 'type' => 'link', 'href' => 'https://crm.omicslogic.com'],
            ['label' => 'Time zone', 'value' => 'Asia/Kolkata (IST)', 'type' => 'text'],
            ['label' => 'Default new-contact stage', 'value' => 'Subscriber', 'type' => 'text'],
        ];

        $deduplicationSettings = [
            ['label' => 'Auto-merge at confidence ≥', 'value' => '0.95', 'type' => 'text'],
            ['label' => 'Send to review band', 'value' => '0.70 – 0.95', 'type' => 'text'],
            ['label' => 'Match on exact email', 'type' => 'toggle', 'enabled' => true],
            ['label' => 'Fuzzy match name + phone', 'type' => 'toggle', 'enabled' => true],
            ['label' => 'Survivorship: newest non-empty wins', 'type' => 'toggle', 'enabled' => true],
        ];
    @endphp

    <section class="settings-hub-page">
        <div class="settings-hub-hero">
            <h1>Settings</h1>
            <p>The rules that keep the database clean, governed, and safe — owned by the admins.</p>
        </div>

        <div class="settings-hub-grid">
            <article class="settings-hub-panel">
                <div class="settings-hub-panel__head">
                    <h2>General</h2>

                    <button type="button" class="settings-hub-edit">
                        <i class="fa-regular fa-pen-to-square"></i>
                        Edit
                    </button>
                </div>

                <dl class="settings-hub-rows">
                    @foreach ($generalSettings as $row)
                        <div class="settings-hub-row">
                            <dt>{{ $row['label'] }}</dt>

                            <dd>
                                @if ($row['type'] === 'link')
                                    <a href="{{ $row['href'] }}" target="_blank" rel="noopener noreferrer">
                                        {{ $row['value'] }}
                                    </a>
                                @else
                                    {{ $row['value'] }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </article>

            <article class="settings-hub-panel">
                <div class="settings-hub-panel__head">
                    <h2>Deduplication rules</h2>

                    <button type="button" class="settings-hub-edit">
                        <i class="fa-regular fa-pen-to-square"></i>
                        Edit
                    </button>
                </div>

                <dl class="settings-hub-rows">
                    @foreach ($deduplicationSettings as $row)
                        <div class="settings-hub-row">
                            <dt>{{ $row['label'] }}</dt>

                            <dd>
                                @if ($row['type'] === 'toggle')
                                    <span
                                        class="settings-hub-toggle {{ $row['enabled'] ? 'settings-hub-toggle--on' : '' }}"
                                        aria-hidden="true"
                                    >
                                        <span class="settings-hub-toggle__thumb"></span>
                                    </span>
                                @else
                                    {{ $row['value'] }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </article>
        </div>
    </section>
</x-admin::layouts>
