@extends('layouts.app')
@section('title', 'Upload Pesanan - PrintShop')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Upload Pesanan</h1>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form method="POST" action="{{ route('customer.orders.store') }}" enctype="multipart/form-data" id="upload-form">
        @csrf

        <div class="mb-6">
            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">File</label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-400 transition-colors"
                 id="drop-zone">
                <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="mt-2 text-sm text-gray-600">Drag & drop file di sini, atau</p>
                <label for="file" class="mt-2 inline-block cursor-pointer text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                    Klik untuk memilih
                </label>
                <input type="file" id="file" name="file" class="hidden"
                       accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png">
                <p class="mt-2 text-xs text-gray-500">PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG (Maks. 100MB)</p>
            </div>
            @error('file')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div id="file-preview" class="hidden mt-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900" id="file-name"></p>
                            <p class="text-xs text-gray-500" id="file-size"></p>
                        </div>
                    </div>
                    <button type="button" id="clear-file" class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan <span class="text-gray-400">(opsional)</span></label>
            <textarea id="notes" name="notes" rows="4" maxlength="1000"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('notes') border-red-500 @enderror"
                      placeholder="Contoh: Tolong cetak warna, 2 rangkap...">{{ old('notes') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Maks. 1000 karakter</p>
            @error('notes')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div id="upload-progress" class="hidden mb-6">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progress-bar" class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p class="mt-1 text-sm text-gray-600" id="progress-text">Mengupload...</p>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('customer.orders.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Batal
            </a>
            <button type="submit" id="submit-btn"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Kirim Pesanan
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file');
    const dropZone = document.getElementById('drop-zone');
    const filePreview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const clearFile = document.getElementById('clear-file');
    const submitBtn = document.getElementById('submit-btn');
    const uploadProgress = document.getElementById('upload-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const form = document.getElementById('upload-form');

    function formatSize(bytes) {
        if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
        return bytes + ' bytes';
    }

    function showFile(file) {
        fileName.textContent = file.name;
        fileSize.textContent = formatSize(file.size);
        filePreview.classList.remove('hidden');
        dropZone.classList.add('hidden');
    }

    function clearFileSelection() {
        fileInput.value = '';
        filePreview.classList.add('hidden');
        dropZone.classList.remove('hidden');
    }

    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) showFile(e.target.files[0]);
    });

    clearFile.addEventListener('click', clearFileSelection);

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-indigo-400', 'bg-indigo-50');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            showFile(e.dataTransfer.files[0]);
        }
    });

    form.addEventListener('submit', function(e) {
        if (!fileInput.files.length) {
            e.preventDefault();
            alert('File wajib diupload');
            return;
        }
        submitBtn.disabled = true;
        submitBtn.textContent = 'Mengupload...';
        uploadProgress.classList.remove('hidden');
    });
});
</script>
@endsection
