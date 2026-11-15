<div class="row">
    <div class="col-lg-4 col-xlg-3 col-md-12">
        <div class="white-box">
            <div class="user-bg"> <img width="100%" alt="user" src="{{ $user->profile_photo_url}}">
                <div class="overlay-box">
                    <div class="user-content">
                        <a href="javascript:void(0)"><img src="{{ $user->profile_photo_url}}" class="thumb-lg img-circle" alt="img"></a>
                        <h4 class="text-white mt-2">{{ $user->name_complete}}</h4>
                        {{--  <h5 class="text-white mt-2"></h5>  --}}
                    </div>
                </div>
            </div>
            <div class="user-btm-box mt-5 d-md-flex">
                {{--  <div class="col-md-4 col-sm-4 text-center">
                    <h1>258</h1>
                    <small>cleintes</small>
                </div>
                <div class="col-md-4 col-sm-4 text-center">
                    <h1>125</h1>
                </div>
                <div class="col-md-4 col-sm-4 text-center">
                    <h1>556</h1>
                </div>  --}}
            </div>
        </div>
    </div>
    <!-- Column -->
    <!-- Column -->
    <div class="col-lg-8 col-xlg-9 col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0 fw-bold">Nombre completo: </h6>
                    </div>
                    <div class="col-sm-9">
                        {{ $user->name_complete}}
                    </div>
                </div>
                <hr>

                <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0 fw-bold">DPI: </h6>
                    </div>
                    <div class="col-sm-9">
                        {{ $user->dni}}
                    </div>
                </div>
                <hr>

                <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0 fw-bold">Dirección: </h6>
                    </div>
                    <div class="col-sm-9">
                        {{ $user->address}}
                    </div>
                </div>
                <hr>

                <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0 fw-bold">Teléfono: </h6>
                    </div>
                    <div class="col-sm-9">
                        {{ $user->phone}}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0 fw-bold">Email: </h6>
                    </div>
                    <div class="col-sm-9">
                        {{ $user->email}}
                    </div>
                </div>
                <hr>
                @isset($user->reference)
                <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0 fw-bold">Referencia: </h6>
                    </div>
                    <div class="col-sm-9">
                        {{ $user->reference}}
                    </div>
                </div>
                <hr>
                @endisset
                @isset($user->ip)
                <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0 fw-bold">IP: </h6>
                    </div>
                    <div class="col-sm-9">
                        {{ $user->ip}}
                    </div>
                </div>
                <hr>
                @endisset
                @isset($user->clave_wifi)
                <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0 fw-bold">Clave Wifi: </h6>
                    </div>
                    <div class="col-sm-9">
                        {{ $user->clave_wifi}}
                    </div>
                </div>
                <hr>
                @endisset
            </div>
        </div>
    </div>
</div>