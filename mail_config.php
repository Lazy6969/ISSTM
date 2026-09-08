<?php
// Configuration SMTP pour l'envoi réel d'emails (OTP de réinitialisation de mot de passe, etc.).
//
// IMPORTANT : remplacez 'password' ci-dessous par un « mot de passe d'application » Google
// (16 caractères), PAS votre mot de passe Gmail habituel. À générer sur le compte
// isstm.univ.umg@gmail.com via : https://myaccount.google.com/apppasswords
// (nécessite que la validation en 2 étapes soit activée sur ce compte Google).
// Tant que ce mot de passe n'est pas renseigné, l'envoi d'email échouera silencieusement
// (isstm_send_mail() renverra false) et le reste du site continuera de fonctionner normalement.
return [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'isstm.univ.umg@gmail.com',
    'password' => 'rrjfsbgaekpmoazb',
    'from_email' => 'isstm.univ.umg@gmail.com',
    'from_name' => 'ISSTM Mahajanga',
];
