## Testing

```bash
vendor/bin/phpstan analyse src tests --level 9
```
```bash
bin/phpunit
```

Either there is an error in readme or fee can be smaller for bigger loan amount, I assumed readme data are correct
. I am talking about "5000 PLN: 100 PLN" breakpoint.

## The test

The requirement is to build a fee calculator that - given a monetary **amount** - will produce an appropriate fee for a loan, based on a fee structure and a set of rules described below. A general contract for this functionality is defined in the interface `FeeCalculator`.

Implement your solution such that it fulfils the requirements.

- The fee structure does not follow a formula.
- Values in between the breakpoints should be interpolated linearly between the lower bound and upper bound that they fall between.
- The number of breakpoints, their values, or storage might change.
- The fee should be rounded up such that fee + loan amount is an exact multiple of 5.
- The minimum amount for a loan is 1,000 PLN, and the maximum is 20,000 PLN and you can assume that it will always be in this range.

Example inputs/outputs:
|Loan amount  |Fee     |
|-------------|--------|
|6500 PLN     |130 PLN |
|19,250 PLN   |385 PLN |

# Installation
A database or any other external dependency is not required for this test.

```bash
composer install --dev
```

# Fee Structure
The fee structure doesn't follow particular algorithm and it is possible that same fee will be applicable for different amounts.

```
1000 PLN: 50 PLN
2000 PLN: 90 PLN
3000 PLN: 90 PLN
4000 PLN: 115 PLN
5000 PLN: 100 PLN
6000 PLN: 120 PLN
7000 PLN: 140 PLN
8000 PLN: 160 PLN
9000 PLN: 180 PLN
10000 PLN: 200 PLN
11000 PLN: 220 PLN
12000 PLN: 240 PLN
13000 PLN: 260 PLN
14000 PLN: 280 PLN
15000 PLN: 300 PLN
16000 PLN: 320 PLN
17000 PLN: 340 PLN
18000 PLN: 360 PLN
19000 PLN: 380 PLN
20000 PLN: 400 PLN
```