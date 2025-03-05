@extends('layouts.app')

@section('content')
    <div id="user-management"></div>
@endsection

@push('scripts')
    <script>
        // Mount the React component
        const container = document.getElementById('user-management');
        if (container) {
            ReactDOM.createRoot(container).render(
                <React.StrictMode>
                    <UserManagement />
                </React.StrictMode>
            );
        }
    </script>
@endpush
