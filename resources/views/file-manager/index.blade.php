<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S3 File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">S3 File Manager</h1>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Current Path: {{ $currentPath ?: 'root' }}</h5>
                            <div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                                    <i class="bi bi-folder-plus"></i> Create Folder
                                </button>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                                    <i class="bi bi-upload"></i> Upload File
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Last Modified</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($folders as $folder)
                                        <tr>
                                            <td>
                                                <i class="bi bi-folder"></i>
                                                <a href="?path={{ $folder['path'] }}">{{ $folder['name'] }}</a>
                                            </td>
                                            <td>Folder</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>
                                                <button class="btn btn-danger btn-sm delete-item" data-path="{{ $folder['path'] }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @foreach($files as $file)
                                        <tr>
                                            <td>
                                                <i class="bi bi-file-earmark"></i>
                                                {{ $file['name'] }}
                                            </td>
                                            <td>File</td>
                                            <td>{{ number_format($file['size'] / 1024, 2) }} KB</td>
                                            <td>{{ $file['last_modified']->format('Y-m-d H:i:s') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @if($file['visibility'] === 'public')
                                                        <button type="button" 
                                                                class="btn btn-info btn-sm get-public-url"
                                                                data-path="{{ $file['path'] }}"
                                                                title="Get Public URL">
                                                            <i class="bi bi-link-45deg"></i>
                                                        </button>
                                                    @endif
                                                    <form action="{{ route('file-manager.update-visibility') }}" 
                                                          method="POST" 
                                                          class="d-inline me-2">
                                                        @csrf
                                                        <input type="hidden" name="path" value="{{ $file['path'] }}">
                                                        <input type="hidden" name="visibility" 
                                                               value="{{ $file['visibility'] === 'public' ? 'private' : 'public' }}">
                                                        <button type="submit" 
                                                                class="btn btn-sm {{ $file['visibility'] === 'public' ? 'btn-success' : 'btn-secondary' }}"
                                                                title="{{ $file['visibility'] === 'public' ? 'Public' : 'Private' }}">
                                                            <i class="bi {{ $file['visibility'] === 'public' ? 'bi-globe' : 'bi-lock' }}"></i>
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-danger btn-sm delete-item"
                                                            data-path="{{ $file['path'] }}"
                                                            title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div class="modal fade" id="createFolderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('file-manager.create-folder') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Folder</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="folder_name" class="form-label">Folder Name</label>
                            <input type="text" class="form-control" id="folder_name" name="folder_name" required>
                            <input type="hidden" name="current_path" value="{{ $currentPath }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Folder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload File Modal -->
    <div class="modal fade" id="uploadFileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('file-manager.upload-file') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upload File</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Select File</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                            <input type="hidden" name="current_path" value="{{ $currentPath }}">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="visibility" name="visibility" value="public" checked>
                                <label class="form-check-label" for="visibility">
                                    Public Access
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Upload File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-item');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this item?')) {
                        const path = this.dataset.path;
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("file-manager.delete-file") }}';

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';

                        const pathInput = document.createElement('input');
                        pathInput.type = 'hidden';
                        pathInput.name = 'path';
                        pathInput.value = path;

                        form.appendChild(csrfToken);
                        form.appendChild(pathInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            // Add public URL functionality
            const publicUrlButtons = document.querySelectorAll('.get-public-url');
            publicUrlButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const path = this.dataset.path;
                    
                    fetch('{{ route("file-manager.get-public-url") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            path: path
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Create a temporary input to copy the URL
                            const input = document.createElement('input');
                            input.value = data.url;
                            document.body.appendChild(input);
                            input.select();
                            document.execCommand('copy');
                            document.body.removeChild(input);

                            // Show success message
                            const alert = document.createElement('div');
                            alert.className = 'alert alert-success alert-dismissible fade show';
                            alert.innerHTML = `
                                Public URL copied to clipboard
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            `;
                            document.querySelector('.container').insertBefore(alert, document.querySelector('.card'));
                        } else {
                            // Show error message
                            const alert = document.createElement('div');
                            alert.className = 'alert alert-danger alert-dismissible fade show';
                            alert.innerHTML = `
                                ${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            `;
                            document.querySelector('.container').insertBefore(alert, document.querySelector('.card'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        });
    </script>
</body>
</html>
