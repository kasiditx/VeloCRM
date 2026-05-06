<x-install-layout :currentStep="5">

    <h1>Create Admin Account</h1>
    <p class="subtitle">
        Create your administrator account. You'll use these credentials to log in to {{ velocrm_app_name() }}.
    </p>

    <form method="POST" action="/install/admin" id="admin-form">
        @csrf

        <div class="form-group">
            <label class="form-label" for="name">Full Name</label>
            <input type="text"
                   class="form-input"
                   id="name"
                   name="name"
                   value="{{ old('name', '') }}"
                   placeholder="Admin User"
                   required>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email Address</label>
            <input type="email"
                   class="form-input"
                   id="email"
                   name="email"
                   value="{{ old('email', '') }}"
                   placeholder="admin@example.com"
                   required>
            <div class="form-hint">This will be your login email</div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password"
                       class="form-input"
                       id="password"
                       name="password"
                       placeholder="••••••••"
                       required
                       minlength="8">
                <div class="form-hint">Minimum 8 characters</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <input type="password"
                       class="form-input"
                       id="password_confirmation"
                       name="password_confirmation"
                       placeholder="••••••••"
                       required
                       minlength="8">
            </div>
        </div>

        <div class="btn-group" style="justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" onclick="this.innerHTML='<span class=\'spinner\'></span> Creating...'; this.disabled=true; this.form.submit();">
                Create Admin & Continue →
            </button>
        </div>
    </form>

</x-install-layout>
