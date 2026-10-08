<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register Host - MiniMon</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 100px;
        }

        .button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
        }

        .button:hover {
            background: #1d4ed8;
        }

        .back {
            display: inline-block;
            margin-left: 10px;
            text-decoration: none;
            color: #333;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Register Host</h1>

    <p>
        Add a server or workstation that will be monitored by MiniMon.
    </p>

    @if ($errors->any())
        <div class="error">
            <strong>Please correct the following:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('hosts.store') }}">

        @csrf

        <div class="form-group">
            <label for="name">Host Name</label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="e.g. Application Server"
                required
            >
        </div>

        <div class="form-group">
            <label for="hostname">Hostname</label>

            <input
                type="text"
                id="hostname"
                name="hostname"
                value="{{ old('hostname') }}"
                placeholder="e.g. server01"
                required
            >
        </div>

        <div class="form-group">
            <label for="ip_address">IP Address</label>

            <input
                type="text"
                id="ip_address"
                name="ip_address"
                value="{{ old('ip_address') }}"
                placeholder="e.g. 192.168.1.10"
                required
            >
        </div>

        <div class="form-group">
            <label for="operating_system">
                Operating System
            </label>

            <input
                type="text"
                id="operating_system"
                name="operating_system"
                value="{{ old('operating_system') }}"
                placeholder="e.g. Windows Server / Linux"
            >
        </div>

        <div class="form-group">
            <label for="architecture">
                Architecture
            </label>

            <select id="architecture" name="architecture">

                <option value="">
                    Select architecture
                </option>

                <option value="x64">
                    x64
                </option>

                <option value="x86">
                    x86
                </option>

                <option value="ARM64">
                    ARM64
                </option>

                <option value="ARM">
                    ARM
                </option>

            </select>
        </div>

        <div class="form-group">
            <label for="description">
                Description
            </label>

            <textarea
                id="description"
                name="description"
                placeholder="Optional description"
            >{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="button">
            Register Host
        </button>

        <a
            href="{{ route('hosts.index') }}"
            class="back"
        >
            Cancel
        </a>

    </form>

</div>

</body>
</html>