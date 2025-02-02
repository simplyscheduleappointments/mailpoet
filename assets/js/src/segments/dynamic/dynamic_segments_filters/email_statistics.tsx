import React, { useState, useEffect, useCallback } from 'react';
import MailPoet from 'mailpoet';
import { assign, compose, find } from 'lodash/fp';

import APIErrorsNotice from 'notices/api_errors_notice';
import Select from 'common/form/react_select/react_select';
import {
  EmailActionTypes,
  EmailFormItem,
  OnFilterChange,
  SelectOption,
} from '../types';
import { SegmentFormData } from '../segment_form_data';

interface Props {
  onChange: OnFilterChange;
  item: EmailFormItem;
}

const shouldDisplayLinks = (itemAction: string, itemNewsletterId?: string): boolean => (
  (
    (itemAction === EmailActionTypes.CLICKED)
    || (itemAction === EmailActionTypes.NOT_CLICKED)
  )
  && (itemNewsletterId != null)
);

const newsletterOptions = SegmentFormData.newslettersList?.map((newsletter) => {
  const sentAt = (newsletter.sent_at) ? MailPoet.Date.format(newsletter.sent_at) : MailPoet.I18n.t('notSentYet');
  return {
    label: `${newsletter.subject} (${sentAt})`,
    value: newsletter.id,
  };
});

export const EmailStatisticsFields: React.FunctionComponent<Props> = ({ onChange, item }) => {
  const [errors, setErrors] = useState([]);
  const [links, setLinks] = useState<SelectOption[]>([]);
  const [loadingLinks, setLoadingLinks] = useState<boolean>(false);

  function loadLinks(newsletterId: string): void {
    setErrors([]);
    setLoadingLinks(true);
    MailPoet.Ajax.post({
      api_version: MailPoet.apiVersion,
      endpoint: 'newsletter_links',
      action: 'get',
      data: { newsletterId },
    })
      .then((response) => {
        const { data } = response;
        const loadedLinks = data.map((link) => ({
          value: link.id,
          label: link.url,
        }));
        setLoadingLinks(false);
        setLinks(loadedLinks);
      })
      .fail((response) => {
        setErrors(response.errors);
      });
  }

  const loadLinksCB = useCallback(() => {
    if (!shouldDisplayLinks(item.action, item.newsletter_id)) return;
    setLinks([]);
    loadLinks(item.newsletter_id);
  }, [item.action, item.newsletter_id]);

  useEffect(() => {
    loadLinksCB();
  }, [loadLinksCB, item.action, item.newsletter_id]);

  return (
    <>
      {(errors.length > 0 && (
        <APIErrorsNotice errors={errors} />
      ))}

      <Select
        isFullWidth
        placeholder={MailPoet.I18n.t('selectNewsletterPlaceholder')}
        options={newsletterOptions}
        value={find(['value', item.newsletter_id], newsletterOptions)}
        onChange={(option: SelectOption): void => compose([
          onChange,
          assign(item),
        ])({ newsletter_id: option.value })}
        automationId="segment-email"
      />
      {(loadingLinks && (MailPoet.I18n.t('loadingDynamicSegmentItems')))}
      {
        (!!links.length && shouldDisplayLinks(item.action, item.newsletter_id))
        && (
          <Select
            isFullWidth
            placeholder={MailPoet.I18n.t('selectLinkPlaceholder')}
            options={links}
            value={find(['value', item.link_id], links)}
            onChange={(option: SelectOption): void => compose([
              onChange,
              assign(item),
            ])({ link_id: option.value })}
          />
        )
      }
    </>
  );
};
