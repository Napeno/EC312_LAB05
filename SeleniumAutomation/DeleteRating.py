from selenium import webdriver
from selenium.webdriver.common.by import By
import time

browser = webdriver.Chrome()

browser.get("https://www.muchmade.id.vn/wp-admin/edit.php?post_type=product&page=product-reviews")

userName = browser.find_element(By.CSS_SELECTOR, "form #user_login")
userName.send_keys("muchmade")

userPass = browser.find_element(By.CSS_SELECTOR, "form #user_pass")
userPass.send_keys("admin123")

loginBtn = browser.find_element(By.CSS_SELECTOR, "#wp-submit")
loginBtn.click()

time.sleep(5)

dropDown = browser.find_element(By.CSS_SELECTOR, "#filter-by-review-rating")
dropDown.click()

ratingOne = browser.find_element(By.CSS_SELECTOR, "#filter-by-review-rating option[value='1']")
ratingOne.click()

filterBtn = browser.find_element(By.CSS_SELECTOR, "#post-query-submit")
filterBtn.click()

checkBox = browser.find_element(By.CSS_SELECTOR, "#cb-select-all-1")
checkBox.click()

dropdownAction = browser.find_element(By.CSS_SELECTOR, "#bulk-action-selector-top")
dropdownAction.click()

actionDelete = browser.find_element(By.CSS_SELECTOR, "#bulk-action-selector-top option[value='trash']")
actionDelete.click()

submitBtn = browser.find_element(By.CSS_SELECTOR, ".top #doaction")
submitBtn.click()
