<x-install-layout :currentStep="3">

    <h1>Database Configuration</h1>
    <p class="subtitle">
        Enter your MySQL database credentials below. Make sure the database already exists.
    </p>

    <form method="POST" action="/install/database" id="db-form">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="db_host">Database Host</label>
                <input type="text"
                       class="form-input"
                       id="db_host"
                       name="db_host"
                       value="{{ old('db_host', '127.0.0.1') }}"
                       placeholder="127.0.0.1"
                       required>
                <div class="form-hint">Usually 127.0.0.1 or localhost</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="db_port">Port</label>
                <input type="text"
                       class="form-input"
                       id="db_port"
                       name="db_port"
                       value="{{ old('db_port', '3306') }}"
                       placeholder="3306"
                       required>
                <div class="form-hint">Default MySQL port: 3306</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="db_database">Database Name</label>
            <input type="text"
                   class="form-input"
                   id="db_database"
                   name="db_database"
                   value="{{ old('db_database', '') }}"
                   placeholder="velocrm"
                   required>
            <div class="form-hint">The database must already exist</div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="db_username">Username</label>
                <input type="text"
                       class="form-input"
                       id="db_username"
                       name="db_username"
                       value="{{ old('db_username', 'root') }}"
                       placeholder="root"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label" for="db_password">Password</label>
                <input type="password"
                       class="form-input"
                       id="db_password"
                       name="db_password"
                       value="{{ old('db_password', '') }}"
                       placeholder="••••••••">
                <div class="form-hint">Leave empty if no password</div>
            </div>
        </div>

        <div class="btn-group">
            <a href="/install/requirements" class="btn btn-secondary">← Back</a>
            <button type="submit" class="btn btn-primary" onclick="this.innerHTML='<span class=\'spinner\'></span> Testing...'; this.disabled=true; this.form.submit();">
                Test Connection & Continue →
            </button>
        </div>
    </form>

</x-install-layout>
