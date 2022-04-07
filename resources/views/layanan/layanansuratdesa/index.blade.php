@extends('layouts.dashboard_template')

@include('layouts.fragments.sweetalert2')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $page_title ?? "Page Title" }}
		<small>{{ $page_description ?? '' }}</small>
	</h1>
	<ol class="breadcrumb">
		<li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
		<li class="active">{{ $page_title }}</li>
	</ol>
</section>

<section class="content container-fluid">

	@include('partials.flash_message')

	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">

				@if(count($surat) > 0)
					<div class="box-body">
						<div class="table-responsive">
							<table class="table table-striped">
								<tr>
									<th>Aksi</th>
									<th>Desa</th>
									<th>Judul Surat</th>
									<th>Nama Penduduk</th>
									<th>Nik</th>
								</tr>

								@foreach($surat as $item)
									<tr>
										<td>
											<a href="{{ route('layanan.suratdesa.downloadsurat', ['idLayanan' => str_slug($item->id_sid), 'id_desa' => str_slug($item->data_desa_id), 'lampiran' => false] ) }}"
												class="btn btn-flat bg-light-blue btn-sm" title="Unduh Surat" target="_blank"><i
													class="fa fa-file-word-o "></i>
											</a>
											<button class="btn btn-flat bg-purple btn-sm daftar-dokumen" title="Daftar Dokumen"
												data-id="{{ $item->id_sid }}" data-desa="{{ $item->data_desa_id }}">
												<i class="fa fa-file"></i>
											</button>
											@if ($item->lampiran != null)
											<a href="{{ route('layanan.suratdesa.downloadsurat', ['idLayanan' => str_slug($item->id_sid), 'id_desa' => str_slug($item->data_desa_id), 'lampiran' => true] ) }}" target="_blank" class="btn btn-social btn-flat bg-olive btn-sm"
												title="Unduh Lampiran"><i class="fa fa-paperclip"></i> Lampiran
											</a>
											@endif

											 
											@if($item->setujui != 1)
												<a href="javascript:;" class="btn btn-social btn-flat bg-light-blue btn-sm setuju"
													title="Setuju" data-id="{{ $item->id_sid }}" data-desa="{{ $item->data_desa_id }}"><i
														class="fa fa-check-square-o"></i> Setujui
												</a>
											@endif
										</td>
										<td>{{ $item->dataDesa->nama }}</td>
										<td>{{ $item->nama_surat }}</td>
										<td>{{ $item->nama_penduduk }}</td>
										<td>{{ $item->nik }}</td>
									</tr>
								@endforeach
							</table>
						</div>

						<div class="box-footer clearfix">
							{!! $surat->links() !!}
						</div>
					</div>
				@else
					<div class="box-body">
						<h3>Data tidak ditemukan.</h3>
					</div>
				@endif
			</div>
		</div>
	</div>
</section>

{{-- modal daftar dokumen --}}
<div class="modal fade modal-dokumen" tabindex="-1" role="dialog" aria-labelledby="modalDaftarDokumen"
	aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title w-100 font-weight-bold">Daftar Dokumen</h4>
			</div>

			<div class="modal-body mx-3">
				<div class="table-responsive">
					<table class="table table-bordered dataTable table-hover tabel-daftar">
						<thead class="bg-gray disabled color-palette">
							<tr>
								<th>No</th>
								<th>Aksi</th>
								<th>Nama Dokumen</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>

			</div>
			<div class="modal-footer d-flex justify-content-center">
				<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

@endsection

@push('scripts')
	@include('forms.delete-modal')

	<script>
		$(function () {
			$('a.setuju').click(function (e) {
				e.preventDefault();
				let that = $(this);
				let id_sid = that.data('id');
				let id_desa = that.data('desa');
				Swal.fire({
					title: 'Login Form',
					html: ` 
					{!! Form::open(['id' => 'surat', 'method' => 'POST', 'route'=>  'layanan.suratdesa.setuju']) !!}
					<input type="hidden" name="id" value=${id_sid} >
					<input type="hidden" name="iddesa" value=${id_desa} >
					{!! Form::close() !!}
					`,
					confirmButtonText: 'Setuju',
					focusConfirm: false,

				}).then((result) => {
					if (result.isConfirmed) {
						$('form#surat').submit();
					}

				})
			});

			$('button.daftar-dokumen').click(function (e) {
				var that = $(this);
				console.log(that.data('data_desa_id'));
				$.ajax({
					method: "Post",
					url: "{{ route('layanan.suratdesa.dokumenajax') }}",
					data: {
						"_token": "{{ csrf_token() }}",
						"id": that.data('id'),
						"id_desa": that.data('desa')
					},
					dataType: "Json",
					success: function (response) {
						$('.modal-dokumen').find('tbody').empty();
						if (response.status == true) {
							$.each(response.data, function (i, value) {
								var row = `
										<tr>
											<td class="padat">${i + 1}</td>
											<td class="aksi"><a href="{{ route('layanan.suratdesa.downloadSyarat') }}?file=${value.path}" class="btn bg-purple btn-flat btn-sm" title="Unduh Dokumen"><i class="fa fa-download"></i></a></td>
											<td>${value.nama}</td>
										</tr>
 								`;
								 $('.modal-dokumen').find('tbody').append(row);
							});
						}



						$('.modal-dokumen').modal('show')
					}
				});
			})


		});
	</script>
@endpush