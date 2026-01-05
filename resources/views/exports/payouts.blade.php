<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Service Provider</th>
            <th>Total Amount (SAR)</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Transferred At</th>
            <th>Created At</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($records as $p)
            <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->serviceProvider->name ?? '-' }}</td>
                <td>{{ $p->total_amount }}</td>
                <td>{{ $p->due_date }}</td>
                <td>{{ $p->status }}</td>
                <td>{{ $p->transferred_at }}</td>
                <td>{{ $p->created_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
