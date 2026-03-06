<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Politique de Confidentialite - PRIMA</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-white">

    <div class="min-h-full flex flex-col">

        {{-- Header --}}
        <header class="h-14 flex items-center justify-center w-full shrink-0">
            <div class="flex items-center justify-between w-full max-w-4xl border-b border-slate-100 h-full px-6">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('logo_sodeci.png') }}" alt="SODECI" class="h-8 w-auto">
                    <span class="text-slate-900 font-semibold tracking-tight">PRIMA</span>
                </div>
                <a href="{{ route('login') }}" class="text-[11px] text-slate-400 hover:text-slate-600 tracking-wide uppercase transition">Connexion</a>
            </div>
        </header>

        {{-- Content --}}
        <div class="flex-1 px-6 py-10">
            <div class="w-full max-w-4xl mx-auto">

                <h1 class="text-3xl font-bold text-slate-900 mb-2">Politique de Confidentialite</h1>
                <p class="text-sm text-slate-400 mb-10">Derniere mise a jour : {{ date('d/m/Y') }}</p>

                <div class="space-y-8 text-[15px] text-slate-700 leading-relaxed">

                    {{-- 1. Introduction --}}
                    <section>
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">1. Introduction</h2>
                        <p>
                            La presente politique de confidentialite decrit comment l'application mobile
                            <strong>PRIMA</strong> (Plateforme de Referentiel et Inventaire de la Mobilite et des Autos),
                            editee par la <strong>SODECI</strong> (Societe de Distribution d'Eau de la Cote d'Ivoire),
                            collecte, utilise et protege les donnees personnelles de ses utilisateurs.
                        </p>
                        <p class="mt-2">
                            PRIMA est une application professionnelle interne destinee exclusivement aux agents et
                            superviseurs habilites par la SODECI et le CIDEC pour la realisation de l'inventaire du
                            parc automobile.
                        </p>
                    </section>

                    {{-- 2. Responsable du traitement --}}
                    <section>
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">2. Responsable du traitement</h2>
                        <div class="bg-slate-50 rounded-lg p-4 text-sm">
                            <p><strong>SODECI</strong> — Societe de Distribution d'Eau de la Cote d'Ivoire</p>
                            <p class="mt-1">1, Avenue Christiani — Treichville, Abidjan, Cote d'Ivoire</p>
                            <p class="mt-1">Email : <a href="mailto:contact@sodeci.ci" class="text-[#2DB56B] underline">contact@sodeci.ci</a></p>
                        </div>
                    </section>

                    {{-- 3. Donnees collectees --}}
                    <section>
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">3. Donnees collectees</h2>
                        <p class="mb-3">Dans le cadre de l'inventaire du parc automobile, l'application collecte les categories de donnees suivantes :</p>

                        <div class="space-y-3">
                            <div class="flex gap-3">
                                <span class="shrink-0 w-6 h-6 rounded-full bg-[#2DB56B]/10 text-[#2DB56B] flex items-center justify-center text-xs font-bold">a</span>
                                <div>
                                    <p class="font-medium text-slate-900">Donnees d'identification de l'utilisateur</p>
                                    <p class="text-sm text-slate-500">Nom, prenom, identifiant de connexion, matricule, organisation, region d'affectation.</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <span class="shrink-0 w-6 h-6 rounded-full bg-[#2DB56B]/10 text-[#2DB56B] flex items-center justify-center text-xs font-bold">b</span>
                                <div>
                                    <p class="font-medium text-slate-900">Donnees vehiculaires</p>
                                    <p class="text-sm text-slate-500">Immatriculation, numero de chassis, marque, modele, couleur, type de carburant, kilometrage, etat general du vehicule.</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <span class="shrink-0 w-6 h-6 rounded-full bg-[#2DB56B]/10 text-[#2DB56B] flex items-center justify-center text-xs font-bold">c</span>
                                <div>
                                    <p class="font-medium text-slate-900">Photographies</p>
                                    <p class="text-sm text-slate-500">Photos des vehicules (face avant, face arriere, flancs, tableau de bord, plaque d'immatriculation) prises via la camera de l'appareil.</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <span class="shrink-0 w-6 h-6 rounded-full bg-[#2DB56B]/10 text-[#2DB56B] flex items-center justify-center text-xs font-bold">d</span>
                                <div>
                                    <p class="font-medium text-slate-900">Donnees de geolocalisation</p>
                                    <p class="text-sm text-slate-500">Coordonnees GPS (latitude, longitude) du lieu d'inventaire pour verifier l'emplacement du vehicule au moment de la saisie.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- 4. Permissions utilisees --}}
                    <section>
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">4. Permissions de l'appareil</h2>
                        <p class="mb-3">L'application requiert les permissions suivantes :</p>
                        <div class="overflow-hidden rounded-lg border border-slate-200">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="text-left px-4 py-2.5 font-medium text-slate-600">Permission</th>
                                        <th class="text-left px-4 py-2.5 font-medium text-slate-600">Utilisation</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium text-slate-900">Camera</td>
                                        <td class="px-4 py-2.5 text-slate-600">Prise de photos des vehicules lors de l'inventaire</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium text-slate-900">Localisation</td>
                                        <td class="px-4 py-2.5 text-slate-600">Enregistrement des coordonnees GPS du lieu d'inventaire</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium text-slate-900">Internet</td>
                                        <td class="px-4 py-2.5 text-slate-600">Synchronisation des donnees avec le serveur central</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-sm text-slate-500 mt-2">Ces permissions sont strictement necessaires au fonctionnement de l'application et ne sont utilisees qu'a des fins professionnelles.</p>
                    </section>

                    {{-- 5. Finalite du traitement --}}
                    <section>
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">5. Finalite du traitement</h2>
                        <p>Les donnees collectees sont utilisees exclusivement pour :</p>
                        <ul class="list-disc pl-5 mt-2 space-y-1 text-slate-600">
                            <li>Realiser l'inventaire physique du parc automobile de la SODECI</li>
                            <li>Constituer un referentiel fiable des vehicules et engins</li>
                            <li>Assurer le suivi et la traçabilite des operations d'inventaire</li>
                            <li>Permettre la validation hierarchique des fiches vehicules</li>
                            <li>Generer des rapports statistiques internes</li>
                        </ul>
                    </section>

                    {{-- 6. Stockage et securite --}}
                    <section>
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">6. Stockage et securite des donnees</h2>
                        <p>Les donnees sont protegees par les mesures suivantes :</p>
                        <ul class="list-disc pl-5 mt-2 space-y-1 text-slate-600">
                            <li>Stockage sur serveurs securises avec chiffrement des communications (HTTPS/TLS)</li>
                            <li>Authentification par jeton securise (token Bearer) avec expiration automatique</li>
                            <li>Stockage local chiffre sur l'appareil (Android Keystore)</li>
                            <li>Acces restreint par role (agent, superviseur, administrateur)</li>
                            <li>Journalisation des actions (audit trail) pour la traçabilite</li>
                            <li>Verrouillage automatique du compte apres tentatives de connexion echouees</li>
                        </ul>
                    </section>

                    {{-- 7. Partage des donnees --}}
                    <section>
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">7. Partage des donnees</h2>
                        <p>
                            Les donnees collectees sont strictement internes a la SODECI.
                            Elles ne sont <strong>ni vendues, ni louees, ni partagees</strong> avec des tiers a des fins
                            commerciales ou publicitaires.
                        </p>
                        <p class="mt-2">
                            Seuls les personnels habilites de la SODECI et du CIDEC ont acces aux donnees,
                            dans la limite de leurs attributions respectives.
                        </p>
                    </section>

                    {{-- 8. Duree de conservation --}}
                    <section>
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">8. Duree de conservation</h2>
                        <p>
                            Les donnees sont conservees pendant la duree necessaire a la realisation de l'inventaire
                            et a la gestion du parc automobile, conformement aux obligations legales et reglementaires
                            en vigueur en Cote d'Ivoire.
                        </p>
                    </section>

                    {{-- 9. Droits des utilisateurs --}}
                    <section>
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">9. Droits des utilisateurs</h2>
                        <p>Conformement a la loi ivoirienne relative a la protection des donnees a caractere personnel, vous disposez des droits suivants :</p>
                        <ul class="list-disc pl-5 mt-2 space-y-1 text-slate-600">
                            <li><strong>Droit d'acces :</strong> obtenir une copie de vos donnees personnelles</li>
                            <li><strong>Droit de rectification :</strong> demander la correction de donnees inexactes</li>
                            <li><strong>Droit de suppression :</strong> demander l'effacement de vos donnees</li>
                            <li><strong>Droit d'opposition :</strong> vous opposer au traitement de vos donnees</li>
                        </ul>
                        <p class="mt-3">
                            Pour exercer ces droits, contactez votre administrateur SODECI ou ecrivez a
                            <a href="mailto:contact@sodeci.ci" class="text-[#2DB56B] underline">contact@sodeci.ci</a>.
                        </p>
                    </section>

                    {{-- 10. Modifications --}}
                    <section>
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">10. Modifications de la politique</h2>
                        <p>
                            La SODECI se reserve le droit de modifier la presente politique de confidentialite
                            a tout moment. Les utilisateurs seront informes de toute modification substantielle.
                            La date de derniere mise a jour est indiquee en haut de cette page.
                        </p>
                    </section>

                </div>
            </div>
        </div>

        {{-- Footer --}}
        <footer class="h-12 flex items-center justify-center px-6 shrink-0">
            <span class="text-[11px] text-slate-300">&copy; {{ date('Y') }} SODECI — PRIMA v1.4</span>
        </footer>
    </div>

</body>
</html>
