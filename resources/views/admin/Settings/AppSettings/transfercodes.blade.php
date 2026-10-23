<main class="app-content">
    <div class="app-title">
        <div>Transfer Code Settings</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="row">
                    <div class="col-lg-12">
                        <form method="post" action="{{ route('updatertransfercodes') }}" id="appinfoform" enctype="multipart/form-data" class="form-horizontal form-bordered">
                            @method('PUT')
                            @csrf
                        
                            <div class="row">
                                <div class="form-group col-md-4 mb-3">
                                  <h5>  <label for="exampleInputEmail1">Code 1</label></h5>
                                    <input class="form-control" type="text" name="code1" value="{{$settings->code1}}"  placeholder="Enter code1 name">
                                    <small class="text-{{ $text }}">Cost of code 1 ({{ $settings->currency }})</small>
                                    <input class="form-control" type="number" step="any" min="0" name="code1cost" value="{{ $settings->code1cost }}" placeholder="Cost of {{ $settings->code1 ?: 'code 1' }}">
                                </div>
                                <div class="form-group col-md-4 mb-3">
                                  <h5>  <label for="exampleInputEmail1">Code 2</label></h5>
                                    <input class="form-control" type="text"  name="code2" value="{{$settings->code2}}"  placeholder="Enter code2 name">
                                    <small class="text-{{ $text }}">Cost of code 2 ({{ $settings->currency }})</small>
                                    <input class="form-control" type="number" step="any" min="0" name="code2cost" value="{{ $settings->code2cost }}" placeholder="Cost of {{ $settings->code2 ?: 'code 2' }}">
                                </div>
                                <div class="form-group col-md-4 mb-3">
                                  <h5>  <label for="exampleInputEmail1">Code 3</label></h5>
                                    <input class="form-control" type="text"  name="code3" value="{{$settings->code3}}"  placeholder="Enter code3 name">
                                    <small class="text-{{ $text }}">Cost of code 3 ({{ $settings->currency }})</small>
                                    <input class="form-control" type="number" step="any" min="0" name="code3cost" value="{{ $settings->code3cost }}" placeholder="Cost of {{ $settings->code3 ?: 'code 3' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6 mb-3">
                                  <h5>  <label for="exampleInputEmail1">Code 4</label></h5>
                                    <input class="form-control" type="text"  name="code4" value="{{$settings->code4}}"  placeholder="Enter code4 name">
                                    <small class="text-{{ $text }}">Cost of code 4 ({{ $settings->currency }})</small>
                                    <input class="form-control" type="number" step="any" min="0" name="code4cost" value="{{ $settings->code4cost }}" placeholder="Cost of {{ $settings->code4 ?: 'code 4' }}">
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                  <h5>  <label for="exampleInputEmail1">Code 5</label></h5>
                                    <input class="form-control" type="text"  name="code5" value="{{$settings->code5}}"  placeholder="Enter code5 name">
                                    <small class="text-{{ $text }}">Cost of code 5 ({{ $settings->currency }})</small>
                                    <input class="form-control" type="number" step="any" min="0" name="code5cost" value="{{ $settings->code5cost }}" placeholder="Cost of {{ $settings->code5 ?: 'code 5' }}">
                                </div>
                            </div>
                                 <div class="row">
                                    <div class="form-group col-md-4 mb-3">
                                      <h5>  <label for="exampleInputEmail1">Code 1 Message</label></h5>
                                      <textarea name="code1message" class="form-control " rows="2">{{ $settings->code1message }}</textarea>
                                      <small class="text-{{ $text }}">This message will be displayed to users on if code1 is on international transfer</small>
                                    </div>
                                    <div class="form-group col-md-4 mb-3">
                                      <h5>  <label for="exampleInputEmail1">Code  2 message</label></h5>
                                      <textarea name="code2message" class="form-control " rows="2">{{ $settings->code2message }}</textarea>
                                      <small class="text-{{ $text }}">This message will be displayed to users on if code2 is on international transfer</small>
                                    </div>
                                    <div class="form-group col-md-4 mb-3">
                                      <h5>  <label for="exampleInputEmail1">Code3 message</label></h5>
                                      <textarea name="code3message" class="form-control " rows="2">{{ $settings->code3message }}</textarea>
                                      <small class="text-{{ $text }}">This message will be displayed to users on if code3 is on international transfer </small>
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="form-group col-md-6 mb-3">
                                      <h5>  <label for="exampleInputEmail1">Code 4 Message</label></h5>
                                      <textarea name="code4message" class="form-control " rows="2">{{ $settings->code4message }}</textarea>
                                      <small class="text-{{ $text }}">This message will be displayed to users on if code4 is on international transfer</small>
                                    </div>
                                    <div class="form-group col-md-6 mb-3">
                                      <h5>  <label for="exampleInputEmail1">Code 5 Message</label></h5>
                                      <textarea name="code5message" class="form-control " rows="2">{{ $settings->code5message }}</textarea>
                                      <small class="text-{{ $text }}">This message will be displayed to users on if code5 is on international transfer</small>
                                    </div>
                                 </div>
                                 <div class="row">
                                <div class="form-group col-md-4 mb-3">
                                    <h5 class="">Turn On/Off code1</h5>
                                    <div class="selectgroup">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="code1status" value="1" class="selectgroup-input"
                                                {{ $settings->code1status == '1' ? 'checked' : '' }}>
                                            <span class="selectgroup-button">On</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="code1status"
                                                {{ $settings->code1status != '1' ? 'checked' : '' }} value="0"
                                                class="selectgroup-input">
                                            <span class="selectgroup-button">Off</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-4 mb-3">
                                    <h5 class="">Turn On/Off code2</h5>
                                    <div class="selectgroup">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="code2status" value="1" class="selectgroup-input"
                                                {{ $settings->code2status == '1' ? 'checked' : '' }}>
                                            <span class="selectgroup-button">On</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="code2status"
                                                {{ $settings->code2status != '1' ? 'checked' : '' }} value="0"
                                                class="selectgroup-input">
                                            <span class="selectgroup-button">Off</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-4 mb-3">
                                    <h5 class="">Turn On/Off code3</h5>
                                    <div class="selectgroup">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="code3status" value="1" class="selectgroup-input"
                                                {{ $settings->code3status == '1' ? 'checked' : '' }}>
                                            <span class="selectgroup-button">On</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="code3status"
                                                {{ $settings->code3status != '1' ? 'checked' : '' }} value="0"
                                                class="selectgroup-input">
                                            <span class="selectgroup-button">Off</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6 mb-3">
                                    <h5 class="">Turn On/Off code4</h5>
                                    <div class="selectgroup">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="code4status" value="1" class="selectgroup-input"
                                                {{ $settings->code4status == '1' ? 'checked' : '' }}>
                                            <span class="selectgroup-button">On</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="code4status"
                                                {{ $settings->code4status != '1' ? 'checked' : '' }} value="0"
                                                class="selectgroup-input">
                                            <span class="selectgroup-button">Off</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <h5 class="">Turn On/Off code5</h5>
                                    <div class="selectgroup">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="code5status" value="1" class="selectgroup-input"
                                                {{ $settings->code5status == '1' ? 'checked' : '' }}>
                                            <span class="selectgroup-button">On</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="code5status"
                                                {{ $settings->code5status != '1' ? 'checked' : '' }} value="0"
                                                class="selectgroup-input">
                                            <span class="selectgroup-button">Off</span>
                                        </label>
                                    </div>
                                </div>
                                <div class=" form-group col-md-4 mb-3">
                                    <h5><label for="sms_verification">OTP Code</label></h5>
                                    <div class="selectgroup">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="otp" value="1" class="selectgroup-input"
                                                {{ $settings->otp == '1' ? 'checked' : '' }}>
                                            <span class="selectgroup-button">On</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="otp"
                                                {{ $settings->otp != '1' ? 'checked' : '' }} value="0"
                                                class="selectgroup-input">
                                            <span class="selectgroup-button">Off</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <h5 class="col-12 mb-2">Tax Code</h5>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="tax_code" class="text-{{ $text }}">Tax Code Name</label>
                                    <input class="form-control" type="text" name="tax_code" value="{{ $settings->tax_code }}" placeholder="Enter tax code name (e.g. Tax Code)">
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="tax_code_cost" class="text-{{ $text }}">Cost of Tax Code ({{ $settings->currency }})</label>
                                    <input class="form-control" type="number" step="any" min="0" name="tax_code_cost" value="{{ $settings->tax_code_cost }}" placeholder="Cost of tax code">
                                </div>
                                <div class="form-group col-md-12 mb-3">
                                    <label for="tax_code_message" class="text-{{ $text }}">Tax Code Message</label>
                                    <textarea name="tax_code_message" class="form-control" rows="2">{{ $settings->tax_code_message }}</textarea>
                                    <small class="text-{{ $text }}">This message will be displayed to users when the tax code is required for a transfer.</small>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <h5 class="">Turn On/Off Tax Code</h5>
                                    <div class="selectgroup">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="tax_code_status" value="1" class="selectgroup-input"
                                                {{ $settings->tax_code_status == '1' ? 'checked' : '' }}>
                                            <span class="selectgroup-button">On</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="tax_code_status"
                                                {{ $settings->tax_code_status != '1' ? 'checked' : '' }} value="0"
                                                class="selectgroup-input">
                                            <span class="selectgroup-button">Off</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="tile-footer">
                                <button class="btn btn-primary" style="width: 100%!important;" type="submit">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>