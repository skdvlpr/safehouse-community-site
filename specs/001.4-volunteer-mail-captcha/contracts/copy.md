# Copy contract: volunteer fields + mail (001.4)

Cite: S-I18N (public it+en; staff mail Italian as specified).

## Form labels (site.php)

| Key | it (intent) | en (intent) |
| :--- | :--- | :--- |
| `site.volunteer.name` | Nome (first name) | First name |
| `site.volunteer.last_name` | Cognome | Last name |
| `site.volunteer.phone` | Telefono (no “opzionale”) | Phone (not optional) |
| `site.volunteer.message` | Messaggio | Message |

## Staff mail (always Italian)

Subject: `Nuova candidatura volontario`

Body structure:

```
Abbiamo ricevuto una nuova candidatura di volontario
Nome: {name} Cognome: {last_name}
Indirizzo email: {email}
Tel.: {phone}
Messaggio: {message}

Website | Safe House
```

## Applicant acknowledgement

| Locale | Meaning (write natural copy at implement; owner may tweak at UAT) |
| :--- | :--- |
| it | Candidatura ricevuta; vi contatteremo al più presto; non rispondere a questo messaggio |
| en | Application received; we will contact you soon; please do not reply to this email |

On-page success flash can stay the existing thank-you line if send succeeded.
