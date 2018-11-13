<div class="col-lg-12">
    <div class="ibox float-e-margins">
        <div class="ibox-content">
            <legend>Datos del afiliado</legend>
            {{--
            <div class="row">
                <div class="col-md-6" :class="{'has-error': errors.has('date_death') }" v-if="canAddDataAffiliate">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">
                                Fecha de Fallecimiento</label>
                        <div class="col-sm-8">
                            <input type="text" name="date_death" v-model="date_death" data-date="true" class="form-control">
                            <i v-show="errors.has('date_death')" class="fa fa-warning text-danger"></i>
                            <span v-show="errors.has('date_death')" class="text-danger">@{{ errors.first('date_death') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" :class="{'has-error': errors.has('degree') }">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Grado</label>
                        <div class="col-sm-8">
                            <select name="degree" class="form-control" v-model="affiliate.degree_id" v-validate.initial="'required'">
                                <option :value="null"></option>
                                <option v-for="(degree, index) in degrees" :value="degree.id">@{{ degree.name }}</option>
                            </select>
                            <i v-show="errors.has('degree')" class="fa fa-warning text-danger"></i>
                            <span v-show="errors.has('degree')" class="text-danger">@{{ errors.first('degree') }}</span>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
    <div class="ibox float-e-margins">
        <div class="ibox-content">
            <form method="get" class="form-horizontal">
                <div class="col-md-12">
                    <legend>Datos del Aportante</legend>
                    <div class="row">
                        <div class="col-md-6" :class="{'has-error': errors.has('contributorType') }">
                            <div class="col-md-4">
                                <label class="control-label">Tipo de Aportante</label>
                            </div>
                            <div class="col-md-8">
                                <select class="form-control" autofocus name="contributorType" @change="changeContributor()" v-model.trim="contributorType" v-validate.initial="'required'">
                                    <option :value="null"></option>
                                    <option v-for="(ct,index) in contributorTypes" :value="ct.id">@{{ct.name}}</option>
                                </select>
                                <i v-show="errors.has('contributorType')" class="fa fa-warning text-danger"></i>
                                <span v-show="errors.has('contributorType')" class="text-danger">@{{ errors.first('contributorType') }}</span>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" :class="{'has-error': errors.has('contributor_identity_card') }">
                                <div class="col-md-4">
                                    <label class="control-label">cédula de identidad</label>
                                </div>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="text" name="contributor_identity_card" v-model.trim="contributor_identity_card" class="form-control" v-validate.initial="'required'">
                                        <span class="input-group-btn">
                                            <button class="btn" :class="errors.has('contributor_identity_card') ? 'btn-danger' : 'btn-primary' " type="button" @click="searchcontributor" type="button" role="button"><i class="fa fa-search"></i></button>
                                        </span>
                                    </div>
                                    <i v-show="errors.has('contributor_identity_card')" class="fa fa-warning text-danger"></i>
                                    <span v-show="errors.has('contributor_identity_card')" class="text-danger">@{{ errors.first('contributor_identity_card') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" :class="{'has-error': errors.has('contributor_city_identity_card') }">
                            <div class="col-md-4">
                                <label class="control-label">Ciudad de Expedición</label>
                            </div>
                            <div class="col-md-8">
                                <select class="form-control" name="contributor_city_identity_card" v-model.trim="contributor_city_identity_card_id" v-validate.initial="'required'">
                                    <option :value="null"></option>
                                    <option v-for="city in cities" :value="city.id">@{{ city.name }}</option>
                                </select>
                                <i v-show="errors.has('contributor_city_identity_card')" class="fa fa-warning text-danger"></i>
                                <span v-show="errors.has('contributor_city_identity_card')" class="text-danger">@{{ errors.first('contributor_city_identity_card') }}</span>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Primer Nombre</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="contributor_first_name" v-model.trim="contributor_first_name" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Segundo Nombre</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="contributor_second_name" v-model.trim="contributor_second_name" class="form-control">
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Apellido Paterno</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="contributor_last_name" v-model.trim="contributor_last_name" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Apellido Materno</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="contributor_mothers_last_name" v-model.trim="contributor_mothers_last_name" class="form-control">
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Apellido de Casada</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" :disabled="contributorIsMale" name="contributor_surname_husband" v-model.trim="contributor_surname_husband"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-4">

                            </div>
                            <div class="col-md-8">

                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6" :class="{'has-error': errors.has('contributor_birth_date') }">
                            <div class="col-md-4">
                                <label class="control-label">Fecha de Nacimiento</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" data-date="true" name="contributor_birth_date" v-model.trim="contributor_birth_date"
                                    v-validate.initial="'required|date_format:DD/MM/YYYY'">
                                <i v-show="errors.has('contributor_birth_date')" class="fa fa-warning text-danger"></i>
                                <span v-show="errors.has('contributor_birth_date')" class="text-danger">@{{ errors.first('contributor_birth_date') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6" :class="{'has-error': errors.has('contributor_gender') }">
                            <div class="col-md-4">
                                <label class="control-label">Genero</label>
                            </div>
                            <div class="col-md-8">
                                <select class="form-control m-b" name="contributor_gender" v-model.trim="contributor_gender" v-validate.initial="'required'">
                                    <option :value="null"></option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                                <i v-show="errors.has('contributor_gender')" class="fa fa-warning text-danger"></i>
                                <span v-show="errors.has('contributor_gender')" class="text-danger">@{{ errors.first('contributor_gender') }}</span>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Teléfono del Aportante</label>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-2">
                                        <button class="btn btn-success" type="button" @click="addPhoneNumber"><i class="fa fa-plus"></i></button>
                                    </div>
                                    <div class="col-md-10">
                                        <div v-for="(phone,index) in contributor_phone_numbers">
                                            <div class="input-group">
                                                <input type="text" name="contributor_phone_number[]" v-model.trim="phone.value" :key="index" class="form-control" data-phone="true">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-danger" v-show="contributor_phone_numbers.length > 1" @click="deletePhoneNumber(index)" type="button"><i class="fa fa-trash"></i></button>
                                                </span>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Celular del Aportante</label>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-2">
                                        <button class="btn btn-success" type="button" @click="addCellPhoneNumber"><i class="fa fa-plus"></i></button>
                                    </div>
                                    <div class="col-md-10">
                                        <div v-for="(cell_phone,index) in contributor_cell_phone_numbers">
                                            <div class="input-group">
                                                <input type="text" name="contributor_cell_phone_number[]" v-model.trim="cell_phone.value" :key="index" class="form-control"
                                                    data-cell-phone="true">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-danger" v-show="contributor_cell_phone_numbers.length > 1" @click="deleteCellPhoneNumber(index)" type="button"><i class="fa fa-trash"></i></button>
                                                </span>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <legend v-if="show_advisor_form">Datos del Tutor Legal</legend>
                    <div class="row" v-if="show_advisor_form">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Nombre de Juzgado</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="advisor_name_court" v-model.trim="advisor_name_court" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Nro de Resolucion</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="advisor_resolution_number" v-model.trim="advisor_resolution_number" class="form-control">
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row" v-if="show_advisor_form">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Fecha de Resolucion</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="advisor_resolution_date" v-model.trim="advisor_resolution_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-4">

                            </div>
                            <div class="col-md-8">

                            </div>
                        </div>
                    </div>
                    <br>

                    <legend v-if=" show_apoderado_form ">Datos del Apoderado</legend>
                    <div class="row" v-if="show_apoderado_form ">
                        <div class="col-md-6" :class="{'has-error': errors.has('legal_guardian_identity_card') }">
                            <div class="col-md-4">
                                <label class="control-label">cédula de identidad</label>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="text" name="legal_guardian_identity_card" v-model.trim="legal_guardian_identity_card" class="form-control" v-validate.initial="'required'">
                                    <span class="input-group-btn">
                                        <button class="btn" :class="errors.has('legal_guardian_identity_card') ? 'btn-danger' : 'btn-primary'" type="button" @click="searchLegalGuardian" type="button" role="button"><i class="fa fa-search"></i></button>
                                    </span>
                                </div>
                                <i v-show="errors.has('legal_guardian_identity_card')" class="fa fa-warning text-danger"></i>
                                <span v-show="errors.has('legal_guardian_identity_card')" class="text-danger">@{{ errors.first('legal_guardian_identity_card') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Ciudad de Expedición</label>
                            </div>
                            <div class="col-md-8">
                                <select class="form-control" name="legal_guardian_city_identity_card" v-model.trim="legal_guardian_city_identity_card">
                                    <option :value="null"></option>
                                    <option v-for="city in cities" :value="city.id">@{{ city.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row" v-if="show_apoderado_form">
                        <div class="col-md-6" :class="{'has-error': errors.has('legal_guardian_first_name') }">
                            <div class="col-md-4">
                                <label class="control-label">Primer Nombre</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="legal_guardian_first_name" v-model.trim="legal_guardian_first_name" class="form-control" v-validate.initial="'required'">
                                <i v-show="errors.has('legal_guardian_first_name')" class="fa fa-warning text-danger"></i>
                                <span v-show="errors.has('legal_guardian_first_name')" class="text-danger">@{{ errors.first('legal_guardian_first_name') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Segundo Nombre</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="legal_guardian_second_name" v-model.trim="legal_guardian_second_name" class="form-control">
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row" v-if=" show_apoderado_form ">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Apellido Paterno</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="legal_guardian_last_name" v-model.trim="legal_guardian_last_name" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Apellido Materno</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="legal_guardian_mothers_last_name" v-model.trim="legal_guardian_mothers_last_name" class="form-control">
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row" v-if=" show_apoderado_form ">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Apellido de Casada</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="legal_guardian_surname_husband" v-model.trim="legal_guardian_surname_husband" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">

                        </div>
                    </div>
                    <br>
                    <div class="row" v-if=" show_apoderado_form ">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Nro de Poder</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="legal_guardian_number_authority" v-model.trim="legal_guardian_number_authority" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class=" control-label">Notaria de Fe Publica Nro</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="legal_guardian_notary_of_public_faith" v-model.trim="legal_guardian_notary_of_public_faith" class="form-control">
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row" v-if=" show_apoderado_form ">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label class="control-label">Notario</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="legal_guardian_notary" v-model.trim="legal_guardian_notary" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-4"></div>
                            <div class="col-md-8"></div>
                        </div>
                    </div>
                </div>
                <div class="row"></div>
            </form>
        </div>
    </div>
    <div class="ibox float-e-margins" v-if="contributionProcess.modality_id == 18" key="pasivo">
        <div class="ibox-content">
            <div class="col-md-12">
                <legend>Datos para la carta de compromiso</legend>
                <div class="row">
                    <div class="col-md-6">
                        <div class="col-md-4">
                            <label class="control-label">Declaración de Pensión</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="pension_declaration" v-model.trim="pension_declaration" class="form-control">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6" :class="{'has-error': errors.has('pension_declaration_date') }">
                        <div class="col-md-4">
                            <label class="control-label">Fecha de Declaración</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" v-date name="pension_declaration_date" v-model.trim="pension_declaration_date" v-validate.initial="'required|date_format:DD/MM/YYYY|max_current_date'">
                            <i v-show="errors.has('pension_declaration_date')" class="fa fa-warning text-danger"></i>
                            <span v-show="errors.has('pension_declaration_date')" class="text-danger">@{{ errors.first('pension_declaration_date') }}</span>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6" :class="{'has-error': errors.has('date_commitment') }">
                        <div class="col-md-4">
                            <label class="control-label">Fecha de compromiso</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" v-date name="date_commitment" v-model.trim="date_commitment" v-validate.initial="'required|date_format:DD/MM/YYYY|max_current_date'">
                            <i v-show="errors.has('date_commitment')" class="fa fa-warning text-danger"></i>
                            <span v-show="errors.has('date_commitment')" class="text-danger">@{{ errors.first('date_commitment') }}</span>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6" :class="{'has-error': errors.has('start_contribution_date') }">
                        <div class="col-md-4">
                            <label class="control-label">Periodo de primer aporte</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" v-date name="start_contribution_date" v-model.trim="start_contribution_date" v-validate.initial="'required|date_format:DD/MM/YYYY|max_current_date'">
                            <i v-show="errors.has('start_contribution_date')" class="fa fa-warning text-danger"></i>
                            <span v-show="errors.has('start_contribution_date')" class="text-danger">@{{ errors.first('start_contribution_date') }}</span>
                        </div>
                    </div>
                </div>
                <br>
            </div>
            <div class="row"></div>
        </div>
    </div>
    <div class="ibox float-e-margins" v-if="contributionProcess.modality_id == 19" key="activo">
        <div class="ibox-content">
            <div class="col-md-12">
                <legend>Datos para el compromiso pago</legend>
                <div class="row">
                    <div class="col-md-6">
                        <div class="col-md-4">
                            <label class="control-label">Tipo</label>
                        </div>
                        <div class="col-md-8">
                            <select name="commitment_type" v-model="commitment_type" class="form-control">
                                    <option v-for="(ct, index) in commitment_types" :value="ct.id">@{{ ct.name }} </option>
                                </select>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <div class="col-md-4">
                            <label class="control-label">Memorandum</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="number" v-model.trim="number" class="form-control">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6" :class="{'has-error': errors.has('commision_date') }">
                        <div class="col-md-4">
                            <label class="control-label">Fecha de Memorandum</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" v-date name="commision_date" v-model.trim="commision_date" v-validate.initial="'required|date_format:DD/MM/YYYY|max_current_date'">
                            <i v-show="errors.has('commision_date')" class="fa fa-warning text-danger"></i>
                            <span v-show="errors.has('commision_date')" class="text-danger">@{{ errors.first('commision_date') }}</span>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <div class="col-md-4">
                            <label class="control-label">Destino</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="destination" v-model.trim="destination" class="form-control">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6" :class="{'has-error': errors.has('commitment_date') }">
                        <div class="col-md-4">
                            <label class="control-label">Fecha de compromiso</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" v-date name="commitment_date" v-model.trim="commitment_date" v-validate.initial="'required|date_format:DD/MM/YYYY|max_current_date'">
                            <i v-show="errors.has('commitment_date')" class="fa fa-warning text-danger"></i>
                            <span v-show="errors.has('commitment_date')" class="text-danger">@{{ errors.first('commitment_date') }}</span>
                        </div>
                    </div>
                </div>
                <br>
            </div>
            <div class="row"></div>
        </div>
    </div>
</div>