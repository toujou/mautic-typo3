CREATE TABLE tt_content (
    mautic_form_id int(11) unsigned DEFAULT '0',
);

CREATE TABLE pages (
    tx_mautic_tags int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_mautic_page_tag_mm (
    uid_local int(11) DEFAULT '0' NOT NULL,
    uid_foreign int(11) DEFAULT '0' NOT NULL,
    sorting int(11) DEFAULT '0' NOT NULL,
    sorting_foreign int(11) DEFAULT '0' NOT NULL,

    KEY uid_local_foreign (uid_local,uid_foreign)
);

CREATE TABLE tx_mautic_domain_model_tag (
    title varchar(255) DEFAULT '' NOT NULL,
    items int(11) DEFAULT '0' NOT NULL,
);
