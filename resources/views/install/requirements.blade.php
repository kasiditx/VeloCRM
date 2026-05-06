<x-install-layout :currentStep="2">

    <h1>Server Requirements</h1>
    <p class="subtitle">
        Let's make sure your server meets all the requirements to run {{ velocrm_app_name() }}.
    </p>

    {{-- PHP Version --}}
    <table class="req-table">
        <thead>
            <tr>
                <th>PHP Version</th>
                <th style="text-align: right;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    PHP {{ $phpVersion }}
                    <div class="form-hint">Required: PHP 8.2 or higher</div>
                </td>
                <td style="text-align: right;">
                    @if ($phpVersionOk)
                        <span class="badge badge-success">✓ Pass</span>
                    @else
                        <span class="badge badge-danger">✕ Fail</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    {{-- PHP Extensions --}}
    <table class="req-table">
        <thead>
            <tr>
                <th>PHP Extension</th>
                <th style="text-align: right;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($extensions as $ext => $loaded)
                <tr>
                    <td>
                        {{ $ext }}
                        @if ($ext === 'gd')
                            <div class="form-hint">GD or Imagick</div>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        @if ($loaded)
                            <span class="badge badge-success">✓ Installed</span>
                        @else
                            <span class="badge badge-danger">✕ Missing</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Folder Permissions --}}
    <table class="req-table">
        <thead>
            <tr>
                <th>Folder Permission</th>
                <th style="text-align: right;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($folders as $folder => $writable)
                <tr>
                    <td>{{ $folder }}</td>
                    <td style="text-align: right;">
                        @if ($writable)
                            <span class="badge badge-success">✓ Writable</span>
                        @else
                            <span class="badge badge-danger">✕ Not Writable</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="btn-group">
        <a href="/install" class="btn btn-secondary">← Back</a>

        @if ($allOk)
            <a href="/install/database" class="btn btn-primary">
                Continue →
            </a>
        @else
            <div>
                <button class="btn btn-primary" disabled>Fix Issues First</button>
                <div class="form-hint" style="margin-top: 0.5rem; text-align: right;">
                    Please resolve all failed checks before continuing
                </div>
            </div>
        @endif
    </div>

</x-install-layout>
