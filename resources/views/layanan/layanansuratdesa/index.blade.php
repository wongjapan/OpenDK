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
											<a href="" class="btn btn-flat bg-purple btn-sm" title="Unduh Surat RTF" target="_blank">
												<i class="fa fa-file-word-o"></i>
											</a>
										
											<a href="http://localhost/afila/premium/index.php/keluar/kirim_kecamatan/6"
												class="btn btn-flat bg-light-blue btn-sm" title="Daftar Dokumen" target="_blank"><i
													class="fa fa-file"></i>
												</a>
												<a href=" " target="_blank" class="btn btn-social btn-flat bg-olive btn-sm"
												title="Unduh Lampiran"><i class="fa fa-paperclip"></i> Lampiran
											</a>
												<a href="javascript:;" class="btn btn-social btn-flat bg-light-blue btn-sm setuju"
												title="Setuju" data-id="{{  $item->id_sid }}" data-desa="{{ $item->data_desa_id }}" ><i class="fa fa-check-square-o"></i> Setujui
											</a>
 

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
		 
		
		});
	</script>
@endpush