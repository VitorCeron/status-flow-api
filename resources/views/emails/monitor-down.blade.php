<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monitor Down Alert</title>
</head>
<body>
    <h2>Monitor Alert: {{ $monitor->name }} is DOWN</h2>

    <p>Your monitor <strong>{{ $monitor->name }}</strong> has reached the failure threshold and is currently <strong>DOWN</strong>.</p>

    <table>
        <tr>
            <td><strong>Monitor Name:</strong></td>
            <td>{{ $monitor->name }}</td>
        </tr>
        <tr>
            <td><strong>URL:</strong></td>
            <td>{{ $monitor->url }}</td>
        </tr>
        <tr>
            <td><strong>Method:</strong></td>
            <td>{{ $monitor->method->value }}</td>
        </tr>
        <tr>
            <td><strong>Fail Threshold:</strong></td>
            <td>{{ $monitor->fail_threshold }} consecutive failure(s)</td>
        </tr>
        <tr>
            <td><strong>Last Checked At:</strong></td>
            <td>{{ $monitor->last_checked_at?->toDateTimeString() }}</td>
        </tr>
    </table>

    <p>Please investigate and resolve the issue as soon as possible.</p>
</body>
</html>
