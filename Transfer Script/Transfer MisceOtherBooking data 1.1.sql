-- Query ExtraCostType 1
INSERT INTO dbo.OtherCostBooking
(
	dbo.OtherCostBooking.cby
    , dbo.OtherCostBooking.cdate
    , dbo.OtherCostBooking.CityId
    , dbo.OtherCostBooking.CompanyId
    , dbo.OtherCostBooking.ConfirmationsId
    
    , dbo.OtherCostBooking.ConfirmCurrency
    , dbo.OtherCostBooking.ConfirmLocal
    , dbo.OtherCostBooking.ConfirmUS
    , dbo.OtherCostBooking.CountryId
    , dbo.OtherCostBooking.CurrencyExtraCostId
    
    , dbo.OtherCostBooking.CurrencyId
    , dbo.OtherCostBooking.CurrencyReductionId
    , dbo.OtherCostBooking.DateRun
    , dbo.OtherCostBooking.DayNo
    , dbo.OtherCostBooking.Detail
    
    , dbo.OtherCostBooking.ExtraCostCurrency
    , dbo.OtherCostBooking.ExtraCostLocal
    , dbo.OtherCostBooking.ExtraCostRemark
    , dbo.OtherCostBooking.ExtraCostUS
    , dbo.OtherCostBooking.OtherCostBookingId
    
    , dbo.OtherCostBooking.OtherCostDesc
    , dbo.OtherCostBooking.OtherCostType
    , dbo.OtherCostBooking.Pax
    , dbo.OtherCostBooking.PayFullAmount
    
    , dbo.OtherCostBooking.QuotationId
    , dbo.OtherCostBooking.ReductionCurrency
    , dbo.OtherCostBooking.ReductionLocal
    , dbo.OtherCostBooking.ReductionRemark
    , dbo.OtherCostBooking.ReductionUS
    
    , dbo.OtherCostBooking.Remark
    , dbo.OtherCostBooking.ServiceCategoryId
    , dbo.OtherCostBooking.[Status]
    , dbo.OtherCostBooking.TourId
    , dbo.OtherCostBooking.uby
    
    , dbo.OtherCostBooking.udate
    , dbo.OtherCostBooking.QuoteUS
    , dbo.OtherCostBooking.QuoteLocal
    , dbo.OtherCostBooking.QuoteCurrency
    , dbo.OtherCostBooking.BookUS
    
    , dbo.OtherCostBooking.BookLocal
    , dbo.OtherCostBooking.BookCurrency
)
SELECT m.cby
    , m.cdate
    , m.CityId
    , m.CompanyId
    , m.ConfirmationsId
    
    , m.ConfirmCurrency
    , m.ConfirmLocal
    , m.ConfirmUS
    , m.CountryId
    , m.CurrencyExtraCostId
    
    , m.CurrencyId
    , m.CurrencyReductionId
    , m.DateRun
    , m.DayNo
    , m.Detail
    
    , m.ExtraCostCurrency
    , m.ExtraCostLocal
    , m.ExtraCostRemark
    , m.ExtraCostUS
    , NEWID() --m.id
    
    --5
    , m.PerPaxDesc --*
    , 1 --*
    , m.Pax
    , m.PayFullAmount
    --6
    , m.QuotationId
    , m.ReductionCurrency
    , m.ReductionLocal
    , m.ReductionRemark
    , m.ReductionUS
    --7
    , m.Remark
    , m.ServiceCategoryId
    , m.[Status]
    , m.TourId
    , m.uby
    --8
    , m.udate
    , m.PerPaxCost
    , m.PerPaxCost
    , 'USD'
    , m.PerPaxCost
    --9
    , m.PerPaxCost
    , 'USD'
FROM dbo.MisceBooking m
WHERE m.PerPaxDesc IS NOT NULL 
	AND m.PerPaxDesc <> ''
-- **************************************

-- Query ExtraCostType 2
INSERT INTO dbo.OtherCostBooking
(
	dbo.OtherCostBooking.cby
    , dbo.OtherCostBooking.cdate
    , dbo.OtherCostBooking.CityId
    , dbo.OtherCostBooking.CompanyId
    , dbo.OtherCostBooking.ConfirmationsId
    
    , dbo.OtherCostBooking.ConfirmCurrency
    , dbo.OtherCostBooking.ConfirmLocal
    , dbo.OtherCostBooking.ConfirmUS
    , dbo.OtherCostBooking.CountryId
    , dbo.OtherCostBooking.CurrencyExtraCostId
    
    , dbo.OtherCostBooking.CurrencyId
    , dbo.OtherCostBooking.CurrencyReductionId
    , dbo.OtherCostBooking.DateRun
    , dbo.OtherCostBooking.DayNo
    , dbo.OtherCostBooking.Detail
    
    , dbo.OtherCostBooking.ExtraCostCurrency
    , dbo.OtherCostBooking.ExtraCostLocal
    , dbo.OtherCostBooking.ExtraCostRemark
    , dbo.OtherCostBooking.ExtraCostUS
    , dbo.OtherCostBooking.OtherCostBookingId
    
    , dbo.OtherCostBooking.OtherCostDesc
    , dbo.OtherCostBooking.OtherCostType
    , dbo.OtherCostBooking.Pax
    , dbo.OtherCostBooking.PayFullAmount
    
    , dbo.OtherCostBooking.QuotationId
    , dbo.OtherCostBooking.ReductionCurrency
    , dbo.OtherCostBooking.ReductionLocal
    , dbo.OtherCostBooking.ReductionRemark
    , dbo.OtherCostBooking.ReductionUS
    
    , dbo.OtherCostBooking.Remark
    , dbo.OtherCostBooking.ServiceCategoryId
    , dbo.OtherCostBooking.[Status]
    , dbo.OtherCostBooking.TourId
    , dbo.OtherCostBooking.uby
    
    , dbo.OtherCostBooking.udate
    , dbo.OtherCostBooking.QuoteUS
    , dbo.OtherCostBooking.QuoteLocal
    , dbo.OtherCostBooking.QuoteCurrency
    , dbo.OtherCostBooking.BookUS
    
    , dbo.OtherCostBooking.BookLocal
    , dbo.OtherCostBooking.BookCurrency
)
SELECT m.cby
    , m.cdate
    , m.CityId
    , m.CompanyId
    , m.ConfirmationsId
    
    , m.ConfirmCurrency
    , m.ConfirmLocal
    , m.ConfirmUS
    , m.CountryId
    , m.CurrencyExtraCostId
    
    , m.CurrencyId
    , m.CurrencyReductionId
    , m.DateRun
    , m.DayNo
    , m.Detail
    
    , m.ExtraCostCurrency
    , m.ExtraCostLocal
    , m.ExtraCostRemark
    , m.ExtraCostUS
    , NEWID() --m.id
    
    --5
    , m.SharedDesc --*
    , 2 --*
    , m.Pax
    , m.PayFullAmount
    --6
    , m.QuotationId
    , m.ReductionCurrency
    , m.ReductionLocal
    , m.ReductionRemark
    , m.ReductionUS
    --7
    , m.Remark
    , m.ServiceCategoryId
    , m.[Status]
    , m.TourId
    , m.uby
    --8
    , m.udate
    , m.SharedCost
    , m.SharedCost
    , 'USD'
    , m.SharedCost
    --9
    , m.SharedCost
    , 'USD'
FROM dbo.MisceBooking m
WHERE m.SharedDesc IS NOT NULL 
	AND m.SharedDesc <> ''
-- **************************************