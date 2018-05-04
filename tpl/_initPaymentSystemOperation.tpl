<!--<script src="{$sMainSiteUrl}js/jquery-1.2.3.pack.js" type="text/javascript"></script>
<script src="{$sMainSiteUrl}js/jquery.validate.js" type="text/javascript"></script>
<script src="{$sMainSiteUrl}js/functions.js" type="text/javascript"></script>      -->
<script src="{$sMainSiteUrl}js/payment.js" type="text/javascript"></script>      

{literal} 
<style type="text/css">
.shipping{display:none;}
</style>
{/literal}                                                                        

<!--<link type="text/css" href="{$sImagesUrl}/iQast_payment_style.css" rel="stylesheet">  -->



<div class="wrapper">
    <!-- <img src="{$sImagesUrl}/00_logo.png" alt="" width="189" height="48" class="logo" /> -->
    <div class="checkoutContainer">
        <div class="containerTop">
            <img src="{$sImagesUrl}/01_mycheckout.jpg" alt="" width="555" height="34" />
            <img src="{$sImagesUrl}/02_step1.jpg" alt="" width="445" height="34" />
        </div>
        
        <div class="content_in">
            {* {$payment_data|@debug_print_var}*}
            <form action="{$url_confirm}" method="POST" id="form_authnet"><!-- target="_blank"-->
                <input type="hidden" name="x_relay_response" value="{$data.x_relay_response}" />
                <input type="hidden" name="x_type" value="{$data.x_type}" />
                <input type="hidden" name="x_login" value="{$data.x_login}" />
                <input type="hidden" name="x_tran_key" value="{$data.x_tran_key}" />
                <input type="hidden" name="x_version" value="{$data.x_version}" />
                <input type="hidden" name="x_amount" value="{$data.x_amount}" />
                <input type="hidden" name="x_invoice_num" value="{$data.x_invoice_num}" />
                <input type="hidden" name="x_method" value="{$data.x_method}" />
                <input type="hidden" name="id" value="{$payment_data.id}" />
                <!--<input type="hidden" id="x_exp_date" name="x_exp_date" value="{$data.x_exp_date}" />-->
                <input type="hidden" name="x_test_request" value="{if $data.x_test_request}TRUE{else}FALSE{/if}" />

                <div class="full">
                    <div class="ccInfo">
                        <div class="inputBlock">
                            <div class="label">{$translates.sel_card_type_c|default:'Select Card Type'}</div>
                            <select class="required" name="card_type" onChange="javascript:generateCC(); return false;">
                                <option {if $payment_data.card_type|default:"" == "Visa"}selected{/if} value="Visa" selected>Visa</option>
                                <option {if $payment_data.card_type|default:"" == "MasterCard"}selected{/if} value="MasterCard">MasterCard</option>
                                <!--<option {if $payment_data.card_type|default:"" == "Discover"}selected{/if} value="Discover">Discover</option>-->
                                <option {if $payment_data.card_type|default:"" == "Amex"}selected{/if} value="Amex">American Express</option>
                            </select>
                        </div>
                        <div class="inputBlockDouble">
                            <div class="halfLabel">{$translates.card_number|default:'Card Number'}</div>
                            <input class="required digits" type="text" id="x_card_num" name="x_card_num" value="{if $auto_submit}{$payment_data.x_card_num}{/if}" />
                            <div class="halfLabel">{$translates.card_code_c|default:'Card Code'}</div>
                            <input class="required digits" type="text" id="x_card_code" name="x_card_code" value="{if $auto_submit}{$payment_data.x_card_code}{/if}" />
                        </div>
                        <div class="inputBlock">
                            <div class="label">{$translates.expir_date|default:'Expiration Date'}</div>
                            
                            <select class="required" name="x_exp_date_month" id="x_exp_date_month" >
                                <option selected="selected" value="">{*$translates.month*}</option>
                                <option {if isset($payment_data.x_exp_date_month) && $payment_data.x_exp_date_month == "01" }selected{/if} value="01">January</option>
                                <option {if isset($payment_data.x_exp_date_month) && $payment_data.x_exp_date_month == "02" }selected{/if} value="02">February</option>
                                <option {if isset($payment_data.x_exp_date_month) && $payment_data.x_exp_date_month == "03" }selected{/if} value="03">March</option>
                                <option {if isset($payment_data.x_exp_date_month) && $payment_data.x_exp_date_month == "04" }selected{/if} value="04">April</option>
                                <option {if isset($payment_data.x_exp_date_month) && $payment_data.x_exp_date_month == "05" }selected{/if} value="05">May</option>
                                <option {if isset($payment_data.x_exp_date_month) && $payment_data.x_exp_date_month == "06" }selected{/if} value="06">June</option>
                                <option {if isset($payment_data.x_exp_date_month) && $payment_data.x_exp_date_month == "07" }selected{/if} value="07">July</option>
                                <option {if isset($payment_data.x_exp_date_month) && $payment_data.x_exp_date_month == "08" }selected{/if} value="08">August</option>
                                <option {if isset($payment_data.x_exp_date_month) && $payment_data.x_exp_date_month == "09" }selected{/if} value="09">September</option>
                                <option {if isset($payment_data.x_exp_date_month) && $payment_data.x_exp_date_month == "10" }selected{/if} value="10">October</option>
                                <option {if isset($payment_data.x_exp_date_month) && $payment_data.x_exp_date_month == "11" }selected{/if} value="11">November</option>
                                <option {if isset($payment_data.x_exp_date_month) && $payment_data.x_exp_date_month == "12" }selected{/if} value="12">December</option>
                            </select>
                            <select class="required" name="x_exp_date_year" id="x_exp_date_year">
                                <option selected="selected" value="">{*$translates.year*}</option>
                                <option {if isset($payment_data.x_exp_date_year) && $payment_data.x_exp_date_year == 10 }selected{/if} value="10">2010</option>
                                <option {if isset($payment_data.x_exp_date_year) && $payment_data.x_exp_date_year == 11 }selected{/if} value="11">2011</option>
                                <option {if isset($payment_data.x_exp_date_year) && $payment_data.x_exp_date_year == 12 }selected{/if} value="12">2012</option>
                                <option {if isset($payment_data.x_exp_date_year) && $payment_data.x_exp_date_year == 13 }selected{/if} value="13">2013</option>
                                <option {if isset($payment_data.x_exp_date_year) && $payment_data.x_exp_date_year == 14 }selected{/if} value="14">2014</option>
                                <option {if isset($payment_data.x_exp_date_year) && $payment_data.x_exp_date_year == 15 }selected{/if} value="15">2015</option>
                                <option {if isset($payment_data.x_exp_date_year) && $payment_data.x_exp_date_year == 16 }selected{/if} value="16">2016</option>
                                <option {if isset($payment_data.x_exp_date_year) && $payment_data.x_exp_date_year == 17 }selected{/if} value="17">2017</option>
                                <option {if isset($payment_data.x_exp_date_year) && $payment_data.x_exp_date_year == 18 }selected{/if} value="18">2018</option>
                                <option {if isset($payment_data.x_exp_date_year) && $payment_data.x_exp_date_year == 19 }selected{/if} value="19">2019</option>
                                <option {if isset($payment_data.x_exp_date_year) && $payment_data.x_exp_date_year == 20 }selected{/if} value="20">2020</option>
                            </select>
                        </div>
                        <input class="" type="hidden" name="x_first_name" value="{$payment_data.x_first_name|default:""}" />
                        <input class="" type="hidden" name="x_last_name" value="{$payment_data.x_last_name|default:""}" />
                        <input class="" type="hidden" name="x_email" value="{$payment_data.x_email|default:""}" />
                        <h2>{$translates.boll_addr|default:'Billing Address'}</h2>
                        <div class="inputBlock">
                            <div class="label">{$translates.company_name|default:'Company Name'}</div>
                            <input type="text" name="x_company" value="{$payment_data.x_company|default:""}" /><div class="halfLabel">(optional)</div>
                        </div>
                        <div class="inputBlock">
                            <div class="label">{$translates.address|default:'Address'}</div>
                            <input class="required" type="text" name="x_address" value="{$payment_data.x_address|default:""}" />
                        </div>
                        <div class="inputBlock">
                            <div class="label">{$translates.city|default:'City'}</div>
                            <input id="smallInput" class="required" type="text" name="x_city" value="{$payment_data.x_city|default:""}" />
                        </div>
                        <div class="inputBlock">
                            <div class="label">{$translates.state|default:'State'}</div>
                            <input class="required" type="text" name="x_state" value="{$payment_data.x_state|default:""}" />
                        </div>
                        <div class="inputBlock">
                            <div class="label">{$translates.country|default:'Country'}</div>
                            <select name="x_country" id="x_country" class="required"> 
                                <option value="">Please select</option> 
                                <option value="United States">United States</option> 
                                <option value="Afghanistan">Afghanistan</option> 
                                <option value="Albania">Albania</option> 
                                <option value="Algeria">Algeria</option> 
                                <option value="American Samoa">American Samoa</option> 
                                <option value="Andorra">Andorra</option> 
                                <option value="Angola">Angola</option> 
                                <option value="Anguilla">Anguilla</option> 
                                <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option> 
                                <option value="Argentina">Argentina</option> 
                                <option value="Armenia">Armenia</option> 
                                <option value="Aruba">Aruba</option> 
                                <option value="Ashmore &amp; Cartier Islands">Ashmore &amp; Cartier Islands</option> 
                                <option value="Australia">Australia</option> 
                                <option value="Austria">Austria</option> 
                                <option value="Azerbaijan">Azerbaijan</option> 
                                <option value="Bahamas, The">Bahamas, The</option> 
                                <option value="Bahrain">Bahrain</option> 
                                <option value="Baker Island">Baker Island</option> 
                                <option value="Bangladesh">Bangladesh</option> 
                                <option value="Barbados">Barbados</option> 
                                <option value="Bassas da India">Bassas da India</option> 
                                <option value="Belarus (White Russia)">Belarus (White Russia)</option> 
                                <option value="Belgium">Belgium</option> 
                                <option value="Belize">Belize</option> 
                                <option value="Benin">Benin</option> 
                                <option value="Bermuda">Bermuda</option> 
                                <option value="Bhutan">Bhutan</option> 
                                <option value="Bolivia">Bolivia</option> 
                                <option value="Bonaire - Netherlands Antilles">Bonaire - Netherlands Antilles</option> 
                                <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option> 
                                <option value="Botswana">Botswana</option> 
                                <option value="Bouvet Island">Bouvet Island</option> 
                                <option value="Brazil">Brazil</option> 
                                <option value="British Indian Ocean Territory">British Indian Ocean Territory</option> 
                                <option value="British Virgin Islands">British Virgin Islands</option> 
                                <option value="Brunei Darussalam">Brunei Darussalam</option> 
                                <option value="Bulgaria">Bulgaria</option> 
                                <option value="Burkina Faso">Burkina Faso</option> 
                                <option value="Burma (Myanmar)">Burma (Myanmar)</option> 
                                <option value="Burundi">Burundi</option> 
                                <option value="Cambodia">Cambodia</option> 
                                <option value="Cameroon">Cameroon</option> 
                                <option value="Canada">Canada</option> 
                                <option value="Cape Verde">Cape Verde</option> 
                                <option value="Cayman Islands">Cayman Islands</option> 
                                <option value="Central African Republic">Central African Republic</option> 
                                <option value="Chad">Chad</option> 
                                <option value="Chile">Chile</option> 
                                <option value="China">China</option> 
                                <option value="Christmas Islands">Christmas Islands</option> 
                                <option value="Clipperton Island">Clipperton Island</option> 
                                <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option> 
                                <option value="Colombia">Colombia</option> 
                                <option value="Comores">Comores</option> 
                                <option value="Congo, Democratic Republic of the">Congo, Democratic Republic of the</option> 
                                <option value="Congo, Republic of the">Congo, Republic of the</option> 
                                <option value="Cook Islands">Cook Islands</option> 
                                <option value="Coral Sea Islands">Coral Sea Islands</option> 
                                <option value="Costa Rica">Costa Rica</option> 
                                <option value="Cote D'Ivoire">Cote D'Ivoire</option> 
                                <option value="Croatia">Croatia</option> 
                                <option value="Cuba">Cuba</option> 
                                <option value="Curacao - Netherlands Antilles">Curacao - Netherlands Antilles</option> 
                                <option value="Cyprus">Cyprus</option> 
                                <option value="Czech Republic">Czech Republic</option> 
                                <option value="Denmark">Denmark</option> 
                                <option value="Djibouti">Djibouti</option> 
                                <option value="Dominica">Dominica</option> 
                                <option value="Dominican Republic">Dominican Republic</option> 
                                <option value="East Timor">East Timor</option> 
                                <option value="Ecuador">Ecuador</option> 
                                <option value="Egypt">Egypt</option> 
                                <option value="El Salvador">El Salvador</option> 
                                <option value="Equatorial Guinea">Equatorial Guinea</option> 
                                <option value="Eritrea">Eritrea</option> 
                                <option value="Estonia">Estonia</option> 
                                <option value="Ethiopia">Ethiopia</option> 
                                <option value="Europa Island">Europa Island</option> 
                                <option value="Falkland Islands">Falkland Islands</option> 
                                <option value="Faroe Islands">Faroe Islands</option> 
                                <option value="Fiji">Fiji</option> 
                                <option value="Finland">Finland</option> 
                                <option value="France">France</option> 
                                <option value="French Guiana">French Guiana</option> 
                                <option value="French Polynesia">French Polynesia</option> 
                                <option value="French Southern &amp; Antarctic Lands">French Southern &amp; Antarctic Lands</option> 
                                <option value="Gabon">Gabon</option> 
                                <option value="Gambia">Gambia</option> 
                                <option value="Gaza Strip">Gaza Strip</option> 
                                <option value="Georgia">Georgia</option> 
                                <option value="Germany">Germany</option> 
                                <option value="Ghana">Ghana</option> 
                                <option value="Gibraltar">Gibraltar</option> 
                                <option value="Greece">Greece</option> 
                                <option value="Greenland">Greenland</option> 
                                <option value="Grenada">Grenada</option> 
                                <option value="Guadeloupe">Guadeloupe</option> 
                                <option value="Guam">Guam</option> 
                                <option value="Guatemala">Guatemala</option> 
                                <option value="Guernsey">Guernsey</option> 
                                <option value="Guinea">Guinea</option> 
                                <option value="Guinea Bissau">Guinea Bissau</option> 
                                <option value="Guyana">Guyana</option> 
                                <option value="Haiti">Haiti</option> 
                                <option value="Heard Island &amp; McDonald Islands">Heard Island &amp; McDonald Islands</option> 
                                <option value="Holy See (Vatican City)">Holy See (Vatican City)</option> 
                                <option value="Honduras">Honduras</option> 
                                <option value="Hong Kong">Hong Kong</option> 
                                <option value="Howland Island">Howland Island</option> 
                                <option value="Hungary">Hungary</option> 
                                <option value="Iceland">Iceland</option> 
                                <option value="India">India</option> 
                                <option value="Indian Ocean">Indian Ocean</option> 
                                <option value="Indonesia">Indonesia</option> 
                                <option value="Iran">Iran</option> 
                                <option value="Iraq">Iraq</option> 
                                <option value="Ireland">Ireland</option> 
                                <option value="Israel">Israel</option> 
                                <option value="Italy">Italy</option> 
                                <option value="Jamaica">Jamaica</option> 
                                <option value="Jan Mayen">Jan Mayen</option>
                                <option value="Japan">Japan</option> 
                                <option value="Jarvis Island">Jarvis Island</option> 
                                <option value="Jersey">Jersey</option> 
                                <option value="Johnston Atoll">Johnston Atoll</option> 
                                <option value="Jordan">Jordan</option> 
                                <option value="Juan de Nova Island">Juan de Nova Island</option> 
                                <option value="Kazakhstan">Kazakhstan</option> 
                                <option value="Kenya">Kenya</option> 
                                <option value="Kingman Reef">Kingman Reef</option> 
                                <option value="Kiribati">Kiribati</option> 
                                <option value="Korea, North">Korea, North</option> 
                                <option value="Korea, South">Korea, South</option> 
                                <option value="Kuwait">Kuwait</option> 
                                <option value="Kyrgyzstan">Kyrgyzstan</option> 
                                <option value="Laos">Laos</option> 
                                <option value="Latvia">Latvia</option> 
                                <option value="Lebanon">Lebanon</option> 
                                <option value="Lesotho">Lesotho</option> 
                                <option value="Liberia">Liberia</option> 
                                <option value="Libya">Libya</option> 
                                <option value="Liechtenstein">Liechtenstein</option> 
                                <option value="Lithuania">Lithuania</option> 
                                <option value="Luxembourg">Luxembourg</option> 
                                <option value="Macau">Macau</option> 
                                <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option> 
                                <option value="Madagascar">Madagascar</option> 
                                <option value="Malawi">Malawi</option> 
                                <option value="Malaysia">Malaysia</option> 
                                <option value="Maldives">Maldives</option> 
                                <option value="Mali">Mali</option> 
                                <option value="Malta">Malta</option> 
                                <option value="Man, Isle of">Man, Isle of</option> 
                                <option value="Marshall Islands">Marshall Islands</option> 
                                <option value="Martinique">Martinique</option> 
                                <option value="Mauritania">Mauritania</option> 
                                <option value="Mauritius">Mauritius</option> 
                                <option value="Mayotte">Mayotte</option> 
                                <option value="Mexico">Mexico</option> 
                                <option value="Micronesia, Federated States of">Micronesia, Federated States of</option> 
                                <option value="Midway Islands">Midway Islands</option> 
                                <option value="Moldova">Moldova</option> 
                                <option value="Monaco">Monaco</option> 
                                <option value="Mongolia">Mongolia</option> 
                                <option value="Montserrat">Montserrat</option> 
                                <option value="Morocco">Morocco</option> 
                                <option value="Mozambique">Mozambique</option> 
                                <option value="Namibia">Namibia</option> 
                                <option value="Nauru">Nauru</option> 
                                <option value="Navassa Island">Navassa Island</option> 
                                <option value="Nepal">Nepal</option> 
                                <option value="Netherlands">Netherlands</option> 
                                <option value="New Caledonia">New Caledonia</option> 
                                <option value="New Zealand">New Zealand</option> 
                                <option value="Nicaragua">Nicaragua</option> 
                                <option value="Niger">Niger</option> 
                                <option value="Nigeria">Nigeria</option> 
                                <option value="Niue">Niue</option> 
                                <option value="Norfolk Island">Norfolk Island</option> 
                                <option value="Northern Mariana Islands">Northern Mariana Islands</option> 
                                <option value="Norway">Norway</option> 
                                <option value="Oman">Oman</option> 
                                <option value="Pakistan">Pakistan</option> 
                                <option value="Palau">Palau</option> 
                                <option value="Palmyra Atoll">Palmyra Atoll</option> 
                                <option value="Panama">Panama</option> 
                                <option value="Papua New Guinea">Papua New Guinea</option> 
                                <option value="Paracel Islands">Paracel Islands</option> 
                                <option value="Paraguay">Paraguay</option> 
                                <option value="Peru">Peru</option> 
                                <option value="Philippines">Philippines</option> 
                                <option value="Pitcairn Islands">Pitcairn Islands</option> 
                                <option value="Poland">Poland</option> 
                                <option value="Portugal">Portugal</option> 
                                <option value="Puerto Rico">Puerto Rico</option> 
                                <option value="Qatar">Qatar</option> 
                                <option value="Reunion">Reunion</option> 
                                <option value="Romania">Romania</option> 
                                <option value="Russia">Russia</option> 
                                <option value="Rwanda">Rwanda</option> 
                                <option value="Saba - Netherlands Antilles">Saba - Netherlands Antilles</option> 
                                <option value="Saipan">Saipan</option> 
                                <option value="Samoa">Samoa</option> 
                                <option value="San Marino">San Marino</option> 
                                <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option> 
                                <option value="Saudi Arabia">Saudi Arabia</option> 
                                <option value="Senegal">Senegal</option> 
                                <option value="Serbia &amp; Montenegro">Serbia &amp; Montenegro</option> 
                                <option value="Seychelles">Seychelles</option> 
                                <option value="Sierra Leone">Sierra Leone</option> 
                                <option value="Singapore">Singapore</option> 
                                <option value="Slovakia">Slovakia</option> 
                                <option value="Slovenia">Slovenia</option> 
                                <option value="Solomon Islands">Solomon Islands</option> 
                                <option value="Somalia">Somalia</option> 
                                <option value="South Africa">South Africa</option> 
                                <option value="South Georgia &amp; South Sandwich Islands">South Georgia &amp; South Sandwich Islands</option> 
                                <option value="Southern Ocean">Southern Ocean</option> 
                                <option value="Spain">Spain</option> 
                                <option value="Spratly Islands">Spratly Islands</option>
                                <option value="Sri Lanka">Sri Lanka</option> 
                                <option value="St. Barthelemy">St. Barthelemy</option> 
                                <option value="St. Eustatius - Netherlands Antilles">St. Eustatius - Netherlands Antilles</option> 
                                <option value="St. Helena">St. Helena</option> 
                                <option value="St. Kitts &amp; Nevis">St. Kitts &amp; Nevis</option> 
                                <option value="St. Lucia">St. Lucia</option> 
                                <option value="St. Maarten - Netherlands Antilles">St. Maarten - Netherlands Antilles</option> 
                                <option value="St. Martin">St. Martin</option> 
                                <option value="St. Pierre &amp; Miquelon">St. Pierre &amp; Miquelon</option> 
                                <option value="St. Vincent &amp; the Grenadines">St. Vincent &amp; the Grenadines</option> 
                                <option value="Sudan">Sudan</option> 
                                <option value="Suriname">Suriname</option> 
                                <option value="Svalbard">Svalbard</option> 
                                <option value="Swaziland">Swaziland</option> 
                                <option value="Sweden">Sweden</option> 
                                <option value="Switzerland">Switzerland</option> 
                                <option value="Syria">Syria</option> 
                                <option value="Tahiti (French Polynesia)">Tahiti (French Polynesia)</option> 
                                <option value="Taiwan">Taiwan</option> 
                                <option value="Tajikistan">Tajikistan</option> 
                                <option value="Tanzania">Tanzania</option> 
                                <option value="Thailand">Thailand</option> 
                                <option value="Tobago">Tobago</option> 
                                <option value="Togo">Togo</option> 
                                <option value="Tokelau">Tokelau</option> 
                                <option value="Tonga Islands">Tonga Islands</option> 
                                <option value="Trinidad">Trinidad</option> 
                                <option value="Tromelin Island">Tromelin Island</option> 
                                <option value="Tunisia">Tunisia</option> 
                                <option value="Turkey">Turkey</option> 
                                <option value="Turkmenistan">Turkmenistan</option> 
                                <option value="Turks &amp; Caicos Islands">Turks &amp; Caicos Islands</option> 
                                <option value="Tuvalu">Tuvalu</option> 
                                <option value="Uganda">Uganda</option> 
                                <option value="Ukraine">Ukraine</option> 
                                <option value="United Arab Emirates">United Arab Emirates</option> 
                                <option value="United Kingdom">United Kingdom</option> 
                                
                                <option value="Uruguay">Uruguay</option> 
                                <option value="Uzbekistan">Uzbekistan</option> 
                                <option value="Vanuatu">Vanuatu</option> 
                                <option value="Vatican City (Holy See)">Vatican City (Holy See)</option> 
                                <option value="Venezuela">Venezuela</option> 
                                <option value="Vietnam">Vietnam</option> 
                                <option value="Virgin Islands">Virgin Islands</option> 
                                <option value="Wake Island">Wake Island</option> 
                                <option value="Wallis &amp; Futuna">Wallis &amp; Futuna</option> 
                                <option value="West Bank">West Bank</option> 
                                <option value="Western Sahara">Western Sahara</option> 
                                <option value="Yemen">Yemen</option> 
                                <option value="Zaire">Zaire</option> 
                                <option value="Zambia">Zambia</option> 
                                <option value="Zimbabwe">Zimbabwe</option> 
                            </select>
                            <script>
                                {literal}
                                $("#x_country option[value={/literal}{$payment_data.x_country}{literal}]").attr("selected", "selected");
                                {/literal}
                            </script>
                        </div>
                        <div class="inputBlock">
                            <div class="label">{$translates.zip|default:'Zip'}</div>
                            <input id="smallInput" class="required" type="text" name="x_zip" value="{$payment_data.x_zip|default:""}" />
                        </div>
                        <div class="inputBlock">
                            <div class="label">{$translates.phone|default:'Phone'}</div>
                            <input class="required" type="text" name="x_phone" value="{$payment_data.x_phone|default:""}" />
                        </div>
                        <div class="inputBlock">
                            <div class="label">Is this your Shipping Address?</div>                           
                            <input type="radio" value="no" name="ship_addr" onclick="{literal}$('.shipping').show();{/literal}"><div class="halfLabel">No</div>
                            <input type="radio" value="yes" name="ship_addr" onclick="{literal}$('.shipping').hide();{/literal}" checked="checked"><div class="halfLabel">Yes</div>
                        </div>
                        
                        <div class="shipping">
                            <div class="inputBlock">
                                <div class="label">{$translates.shipaddress|default:'Shipping Address'}</div>
                                <input class="" type="text" name="x_ship_to_address" value="{$payment_data.x_ship_to_address|default:""}" />
                            </div>
                            <div class="inputBlock">
                                <div class="label">{$translates.shipcity|default:'Shipping City'}</div>
                                <input id="smallInput" class="" type="text" name="x_ship_to_city" value="{$payment_data.x_ship_to_city|default:""}" />
                            </div>
                            <div class="inputBlock">
                                <div class="label">{$translates.shipstate|default:'Shipping State'}</div>
                                <input class="" type="text" name="x_ship_to_state" value="{$payment_data.x_ship_to_state|default:""}" />
                            </div>
                            <div class="inputBlock">
                                <div class="label">{$translates.shipcountry|default:'Shipping Country'}</div>
                                <select name="x_ship_to_country" id="x_ship_to_country" class=""> 
                                    <option value="">Please select</option> 
                                    <option value="United States">United States</option> 
                                    <option value="Afghanistan">Afghanistan</option> 
                                    <option value="Albania">Albania</option> 
                                    <option value="Algeria">Algeria</option> 
                                    <option value="American Samoa">American Samoa</option> 
                                    <option value="Andorra">Andorra</option> 
                                    <option value="Angola">Angola</option> 
                                    <option value="Anguilla">Anguilla</option> 
                                    <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option> 
                                    <option value="Argentina">Argentina</option> 
                                    <option value="Armenia">Armenia</option> 
                                    <option value="Aruba">Aruba</option> 
                                    <option value="Ashmore &amp; Cartier Islands">Ashmore &amp; Cartier Islands</option> 
                                    <option value="Australia">Australia</option> 
                                    <option value="Austria">Austria</option> 
                                    <option value="Azerbaijan">Azerbaijan</option> 
                                    <option value="Bahamas, The">Bahamas, The</option> 
                                    <option value="Bahrain">Bahrain</option> 
                                    <option value="Baker Island">Baker Island</option> 
                                    <option value="Bangladesh">Bangladesh</option> 
                                    <option value="Barbados">Barbados</option> 
                                    <option value="Bassas da India">Bassas da India</option> 
                                    <option value="Belarus (White Russia)">Belarus (White Russia)</option> 
                                    <option value="Belgium">Belgium</option> 
                                    <option value="Belize">Belize</option> 
                                    <option value="Benin">Benin</option> 
                                    <option value="Bermuda">Bermuda</option> 
                                    <option value="Bhutan">Bhutan</option> 
                                    <option value="Bolivia">Bolivia</option> 
                                    <option value="Bonaire - Netherlands Antilles">Bonaire - Netherlands Antilles</option> 
                                    <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option> 
                                    <option value="Botswana">Botswana</option> 
                                    <option value="Bouvet Island">Bouvet Island</option> 
                                    <option value="Brazil">Brazil</option> 
                                    <option value="British Indian Ocean Territory">British Indian Ocean Territory</option> 
                                    <option value="British Virgin Islands">British Virgin Islands</option> 
                                    <option value="Brunei Darussalam">Brunei Darussalam</option> 
                                    <option value="Bulgaria">Bulgaria</option> 
                                    <option value="Burkina Faso">Burkina Faso</option> 
                                    <option value="Burma (Myanmar)">Burma (Myanmar)</option> 
                                    <option value="Burundi">Burundi</option> 
                                    <option value="Cambodia">Cambodia</option> 
                                    <option value="Cameroon">Cameroon</option> 
                                    <option value="Canada">Canada</option> 
                                    <option value="Cape Verde">Cape Verde</option> 
                                    <option value="Cayman Islands">Cayman Islands</option> 
                                    <option value="Central African Republic">Central African Republic</option> 
                                    <option value="Chad">Chad</option> 
                                    <option value="Chile">Chile</option> 
                                    <option value="China">China</option> 
                                    <option value="Christmas Islands">Christmas Islands</option> 
                                    <option value="Clipperton Island">Clipperton Island</option> 
                                    <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option> 
                                    <option value="Colombia">Colombia</option> 
                                    <option value="Comores">Comores</option> 
                                    <option value="Congo, Democratic Republic of the">Congo, Democratic Republic of the</option> 
                                    <option value="Congo, Republic of the">Congo, Republic of the</option> 
                                    <option value="Cook Islands">Cook Islands</option> 
                                    <option value="Coral Sea Islands">Coral Sea Islands</option> 
                                    <option value="Costa Rica">Costa Rica</option> 
                                    <option value="Cote D'Ivoire">Cote D'Ivoire</option> 
                                    <option value="Croatia">Croatia</option> 
                                    <option value="Cuba">Cuba</option> 
                                    <option value="Curacao - Netherlands Antilles">Curacao - Netherlands Antilles</option> 
                                    <option value="Cyprus">Cyprus</option> 
                                    <option value="Czech Republic">Czech Republic</option> 
                                    <option value="Denmark">Denmark</option> 
                                    <option value="Djibouti">Djibouti</option> 
                                    <option value="Dominica">Dominica</option> 
                                    <option value="Dominican Republic">Dominican Republic</option> 
                                    <option value="East Timor">East Timor</option> 
                                    <option value="Ecuador">Ecuador</option> 
                                    <option value="Egypt">Egypt</option> 
                                    <option value="El Salvador">El Salvador</option> 
                                    <option value="Equatorial Guinea">Equatorial Guinea</option> 
                                    <option value="Eritrea">Eritrea</option> 
                                    <option value="Estonia">Estonia</option> 
                                    <option value="Ethiopia">Ethiopia</option> 
                                    <option value="Europa Island">Europa Island</option> 
                                    <option value="Falkland Islands">Falkland Islands</option> 
                                    <option value="Faroe Islands">Faroe Islands</option> 
                                    <option value="Fiji">Fiji</option> 
                                    <option value="Finland">Finland</option> 
                                    <option value="France">France</option> 
                                    <option value="French Guiana">French Guiana</option> 
                                    <option value="French Polynesia">French Polynesia</option> 
                                    <option value="French Southern &amp; Antarctic Lands">French Southern &amp; Antarctic Lands</option> 
                                    <option value="Gabon">Gabon</option> 
                                    <option value="Gambia">Gambia</option> 
                                    <option value="Gaza Strip">Gaza Strip</option> 
                                    <option value="Georgia">Georgia</option> 
                                    <option value="Germany">Germany</option> 
                                    <option value="Ghana">Ghana</option> 
                                    <option value="Gibraltar">Gibraltar</option> 
                                    <option value="Greece">Greece</option> 
                                    <option value="Greenland">Greenland</option> 
                                    <option value="Grenada">Grenada</option> 
                                    <option value="Guadeloupe">Guadeloupe</option> 
                                    <option value="Guam">Guam</option> 
                                    <option value="Guatemala">Guatemala</option> 
                                    <option value="Guernsey">Guernsey</option> 
                                    <option value="Guinea">Guinea</option> 
                                    <option value="Guinea Bissau">Guinea Bissau</option> 
                                    <option value="Guyana">Guyana</option> 
                                    <option value="Haiti">Haiti</option> 
                                    <option value="Heard Island &amp; McDonald Islands">Heard Island &amp; McDonald Islands</option> 
                                    <option value="Holy See (Vatican City)">Holy See (Vatican City)</option> 
                                    <option value="Honduras">Honduras</option> 
                                    <option value="Hong Kong">Hong Kong</option> 
                                    <option value="Howland Island">Howland Island</option> 
                                    <option value="Hungary">Hungary</option> 
                                    <option value="Iceland">Iceland</option> 
                                    <option value="India">India</option> 
                                    <option value="Indian Ocean">Indian Ocean</option> 
                                    <option value="Indonesia">Indonesia</option> 
                                    <option value="Iran">Iran</option> 
                                    <option value="Iraq">Iraq</option> 
                                    <option value="Ireland">Ireland</option> 
                                    <option value="Israel">Israel</option> 
                                    <option value="Italy">Italy</option> 
                                    <option value="Jamaica">Jamaica</option> 
                                    <option value="Jan Mayen">Jan Mayen</option>
                                    <option value="Japan">Japan</option> 
                                    <option value="Jarvis Island">Jarvis Island</option> 
                                    <option value="Jersey">Jersey</option> 
                                    <option value="Johnston Atoll">Johnston Atoll</option> 
                                    <option value="Jordan">Jordan</option> 
                                    <option value="Juan de Nova Island">Juan de Nova Island</option> 
                                    <option value="Kazakhstan">Kazakhstan</option> 
                                    <option value="Kenya">Kenya</option> 
                                    <option value="Kingman Reef">Kingman Reef</option> 
                                    <option value="Kiribati">Kiribati</option> 
                                    <option value="Korea, North">Korea, North</option> 
                                    <option value="Korea, South">Korea, South</option> 
                                    <option value="Kuwait">Kuwait</option> 
                                    <option value="Kyrgyzstan">Kyrgyzstan</option> 
                                    <option value="Laos">Laos</option> 
                                    <option value="Latvia">Latvia</option> 
                                    <option value="Lebanon">Lebanon</option> 
                                    <option value="Lesotho">Lesotho</option> 
                                    <option value="Liberia">Liberia</option> 
                                    <option value="Libya">Libya</option> 
                                    <option value="Liechtenstein">Liechtenstein</option> 
                                    <option value="Lithuania">Lithuania</option> 
                                    <option value="Luxembourg">Luxembourg</option> 
                                    <option value="Macau">Macau</option> 
                                    <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option> 
                                    <option value="Madagascar">Madagascar</option> 
                                    <option value="Malawi">Malawi</option> 
                                    <option value="Malaysia">Malaysia</option> 
                                    <option value="Maldives">Maldives</option> 
                                    <option value="Mali">Mali</option> 
                                    <option value="Malta">Malta</option> 
                                    <option value="Man, Isle of">Man, Isle of</option> 
                                    <option value="Marshall Islands">Marshall Islands</option> 
                                    <option value="Martinique">Martinique</option> 
                                    <option value="Mauritania">Mauritania</option> 
                                    <option value="Mauritius">Mauritius</option> 
                                    <option value="Mayotte">Mayotte</option> 
                                    <option value="Mexico">Mexico</option> 
                                    <option value="Micronesia, Federated States of">Micronesia, Federated States of</option> 
                                    <option value="Midway Islands">Midway Islands</option> 
                                    <option value="Moldova">Moldova</option> 
                                    <option value="Monaco">Monaco</option> 
                                    <option value="Mongolia">Mongolia</option> 
                                    <option value="Montserrat">Montserrat</option> 
                                    <option value="Morocco">Morocco</option> 
                                    <option value="Mozambique">Mozambique</option> 
                                    <option value="Namibia">Namibia</option> 
                                    <option value="Nauru">Nauru</option> 
                                    <option value="Navassa Island">Navassa Island</option> 
                                    <option value="Nepal">Nepal</option> 
                                    <option value="Netherlands">Netherlands</option> 
                                    <option value="New Caledonia">New Caledonia</option> 
                                    <option value="New Zealand">New Zealand</option> 
                                    <option value="Nicaragua">Nicaragua</option> 
                                    <option value="Niger">Niger</option> 
                                    <option value="Nigeria">Nigeria</option> 
                                    <option value="Niue">Niue</option> 
                                    <option value="Norfolk Island">Norfolk Island</option> 
                                    <option value="Northern Mariana Islands">Northern Mariana Islands</option> 
                                    <option value="Norway">Norway</option> 
                                    <option value="Oman">Oman</option> 
                                    <option value="Pakistan">Pakistan</option> 
                                    <option value="Palau">Palau</option> 
                                    <option value="Palmyra Atoll">Palmyra Atoll</option> 
                                    <option value="Panama">Panama</option> 
                                    <option value="Papua New Guinea">Papua New Guinea</option> 
                                    <option value="Paracel Islands">Paracel Islands</option> 
                                    <option value="Paraguay">Paraguay</option> 
                                    <option value="Peru">Peru</option> 
                                    <option value="Philippines">Philippines</option> 
                                    <option value="Pitcairn Islands">Pitcairn Islands</option> 
                                    <option value="Poland">Poland</option> 
                                    <option value="Portugal">Portugal</option> 
                                    <option value="Puerto Rico">Puerto Rico</option> 
                                    <option value="Qatar">Qatar</option> 
                                    <option value="Reunion">Reunion</option> 
                                    <option value="Romania">Romania</option> 
                                    <option value="Russia">Russia</option> 
                                    <option value="Rwanda">Rwanda</option> 
                                    <option value="Saba - Netherlands Antilles">Saba - Netherlands Antilles</option> 
                                    <option value="Saipan">Saipan</option> 
                                    <option value="Samoa">Samoa</option> 
                                    <option value="San Marino">San Marino</option> 
                                    <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option> 
                                    <option value="Saudi Arabia">Saudi Arabia</option> 
                                    <option value="Senegal">Senegal</option> 
                                    <option value="Serbia &amp; Montenegro">Serbia &amp; Montenegro</option> 
                                    <option value="Seychelles">Seychelles</option> 
                                    <option value="Sierra Leone">Sierra Leone</option> 
                                    <option value="Singapore">Singapore</option> 
                                    <option value="Slovakia">Slovakia</option> 
                                    <option value="Slovenia">Slovenia</option> 
                                    <option value="Solomon Islands">Solomon Islands</option> 
                                    <option value="Somalia">Somalia</option> 
                                    <option value="South Africa">South Africa</option> 
                                    <option value="South Georgia &amp; South Sandwich Islands">South Georgia &amp; South Sandwich Islands</option> 
                                    <option value="Southern Ocean">Southern Ocean</option> 
                                    <option value="Spain">Spain</option> 
                                    <option value="Spratly Islands">Spratly Islands</option>
                                    <option value="Sri Lanka">Sri Lanka</option> 
                                    <option value="St. Barthelemy">St. Barthelemy</option> 
                                    <option value="St. Eustatius - Netherlands Antilles">St. Eustatius - Netherlands Antilles</option> 
                                    <option value="St. Helena">St. Helena</option> 
                                    <option value="St. Kitts &amp; Nevis">St. Kitts &amp; Nevis</option> 
                                    <option value="St. Lucia">St. Lucia</option> 
                                    <option value="St. Maarten - Netherlands Antilles">St. Maarten - Netherlands Antilles</option> 
                                    <option value="St. Martin">St. Martin</option> 
                                    <option value="St. Pierre &amp; Miquelon">St. Pierre &amp; Miquelon</option> 
                                    <option value="St. Vincent &amp; the Grenadines">St. Vincent &amp; the Grenadines</option> 
                                    <option value="Sudan">Sudan</option> 
                                    <option value="Suriname">Suriname</option> 
                                    <option value="Svalbard">Svalbard</option> 
                                    <option value="Swaziland">Swaziland</option> 
                                    <option value="Sweden">Sweden</option> 
                                    <option value="Switzerland">Switzerland</option> 
                                    <option value="Syria">Syria</option> 
                                    <option value="Tahiti (French Polynesia)">Tahiti (French Polynesia)</option> 
                                    <option value="Taiwan">Taiwan</option> 
                                    <option value="Tajikistan">Tajikistan</option> 
                                    <option value="Tanzania">Tanzania</option> 
                                    <option value="Thailand">Thailand</option> 
                                    <option value="Tobago">Tobago</option> 
                                    <option value="Togo">Togo</option> 
                                    <option value="Tokelau">Tokelau</option> 
                                    <option value="Tonga Islands">Tonga Islands</option> 
                                    <option value="Trinidad">Trinidad</option> 
                                    <option value="Tromelin Island">Tromelin Island</option> 
                                    <option value="Tunisia">Tunisia</option> 
                                    <option value="Turkey">Turkey</option> 
                                    <option value="Turkmenistan">Turkmenistan</option> 
                                    <option value="Turks &amp; Caicos Islands">Turks &amp; Caicos Islands</option> 
                                    <option value="Tuvalu">Tuvalu</option> 
                                    <option value="Uganda">Uganda</option> 
                                    <option value="Ukraine">Ukraine</option> 
                                    <option value="United Arab Emirates">United Arab Emirates</option> 
                                    <option value="United Kingdom">United Kingdom</option> 
                                  
                                    <option value="Uruguay">Uruguay</option> 
                                    <option value="Uzbekistan">Uzbekistan</option> 
                                    <option value="Vanuatu">Vanuatu</option> 
                                    <option value="Vatican City (Holy See)">Vatican City (Holy See)</option> 
                                    <option value="Venezuela">Venezuela</option> 
                                    <option value="Vietnam">Vietnam</option> 
                                    <option value="Virgin Islands">Virgin Islands</option> 
                                    <option value="Wake Island">Wake Island</option> 
                                    <option value="Wallis &amp; Futuna">Wallis &amp; Futuna</option> 
                                    <option value="West Bank">West Bank</option> 
                                    <option value="Western Sahara">Western Sahara</option> 
                                    <option value="Yemen">Yemen</option> 
                                    <option value="Zaire">Zaire</option> 
                                    <option value="Zambia">Zambia</option> 
                                    <option value="Zimbabwe">Zimbabwe</option> 
                                </select>
                                <script>
                                    {literal}
                                    $("#x_ship_to_country option[value={/literal}{$payment_data.x_ship_to_country}{literal}]").attr("selected", "selected");
                                    {/literal}
                                </script>
                            </div>
                            <div class="inputBlock">
                                <div class="label">{$translates.shipzip|default:'Shipping Zip'}</div>
                                <input id="smallInput" class="" type="text" name="x_ship_to_zip" value="{$payment_data.x_ship_to_zip|default:""}" />
                            </div>
                            <div class="inputBlock">
                                <div class="label">{$translates.shipphone|default:'Shipping Phone'}</div>
                                <input class="" type="text" name="x_ship_to_phone" value="{$payment_data.x_ship_to_phone|default:""}" />
                            </div>
                        </div>
                    <div>
                    {*<div class="contentButtons">
                        <input type="button" class="butt_back_page" onClick="location.href='{$settings.payment.iqastReserveNamesBackUrl}';" value="{$translates.back_page|default:"Back"}" /> onClick="history.go(-1)" 
                        <input type="submit" class="butt_cont_page"  onclick="validateFormAuthNet('form_authnet');" value="{$translates.next_step|default:"Next Step"}" />
                    </div><!-- contentButtons -->*}
                    <div class="contentButtons">
                        <input type="hidden" name="payment_system_name" value="{$payment_system_name}" />
                        <input type="hidden" name="order_id" value="{$order_id}" />
                        <input type="button" class="backbtn" onClick="document.getELementById('form_back').submit();" value="{$translates.b_back|default:"Back"}" />
                        <input type="submit" class="butt_verif_page" onclick="validateFormAuthNet('form_authnet');" value="{$translates.verif_ord|default:"Verify"}" />
                        {*<input type="submit" class="butt_verif_page" name="confirmed" value="{$translates.verif_ord|default:"Verify"}" />continuebtn *}
                    </div>
                    <div class="error errors" id="errors">&nbsp;</div>
        </form>
        
        <form action="{$url_back}" method="post" id="form_back" name="form_back">
            <input type="hidden" name="back" value="1" />
        </form>


{literal}
<script type="text/javascript">
    $("#form_authnet").validate({
        rules: {
            x_card_num: {
                rangelength: [15,16]
                //,
                //remote: ("{/literal}{$sMainSiteUrl}{literal}index.php?s=check_card")          
            },
            x_card_code: {
                rangelength: [3,4]
            }
        }
    });
</script>
{/literal}