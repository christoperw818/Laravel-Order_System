<footer>
    @auth
        @if (auth()->user()->user_type == 'employer')
            <div class="footer_wrap" style="background: #fff; margin-top: 130px;">
                <div class="footer">
                    <div class="row">
                        <div class="col-xl-8 col-12">
                            <ul class="copyright_list">
                                <li>
                                    <p>
                                    <p style="color: #aaa"><span><span class="footer_start_sym">©</span> Lion Werbe GmbH | Wir
                                            machen Werbung.</span>
                                        <span>Stark wie ein Löwe.</span> <span>Alle Rechte vorbehalten.</span>
                                    </p>
                                    </p>
                                </li>
                            </ul>
                        </div>
                        <div class="col-xl-4 col-12">
                            <ul class="copyright_list copyright_list2">
                                <li>
                                    <a href="" style="color: #aaa">Datenschutzerklärung</a>
                                </li>
                                <li>
                                    <a href="" style="color: #aaa">Impressum</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="footer_wrap" style="background: #fff">
                <div class="footer">
                    <div class="row">
                        <div class="col-xl-8 col-12">
                            <ul class="copyright_list">
                                <li>
                                    <p>
                                    <p style="color: #aaa"><span><span class="footer_start_sym">©</span> Lion Werbe GmbH | Wir
                                            machen Werbung.</span>
                                        <span>Stark wie ein Löwe.</span> <span>Alle Rechte vorbehalten.</span>
                                    </p>
                                    </p>
                                </li>
                            </ul>
                        </div>
                        <div class="col-xl-4 col-12">
                            <ul class="copyright_list copyright_list2">
                                <li>
                                    <a href="" style="color: #aaa">Datenschutzerklärung</a>
                                </li>
                                <li>
                                    <a href="" style="color: #aaa">Impressum</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
    @endauth
</footer>
